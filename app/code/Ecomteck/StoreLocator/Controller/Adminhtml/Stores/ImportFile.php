<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Controller\Adminhtml\Stores;

use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\File\Csv;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;
use Ecomteck\StoreLocator\Controller\Adminhtml\Stores;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Ecomteck\StoreLocator\Model\Uploader;
use Ecomteck\StoreLocator\Model\UploaderPool;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite as BaseUrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Ecomteck\StoreLocator\Model\Config as StoreLocatorConfig;

class ImportFile extends Stores
{
    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    public $uploaderPool;
    
    /**
     * @var csvProcessor
     */
    public $csvProcessor;


    /**
     * @var BaseUrlRewrite
     */
    protected $urlRewrite;

    /**
     * Url rewrite service
     *
     * @var $urlRewriteService
     */
    protected $urlRewriteService;

    /**
     * Url finder
     *
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /**
     * Configuration
     *
     * @var StoreLocator
     */
    protected $storelocatorConfig;

    /**
     * StoresInterfaceFactory
     *
     * @var StoreLocator
     */
    protected $storesFactory;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /**

     */
    public function __construct(
        Registry $registry,
        StoresRepositoryInterface $storesRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        StoresInterfaceFactory $storesFactory,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool,
        BaseUrlRewrite $urlRewrite,
        UrlRewriteService $urlRewriteService,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        StoreLocatorConfig $storelocatorConfig,
        UrlRewriteFactory $urlRewriteFactory,
        Csv $csvProcessor,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->urlRewrite = $urlRewrite;
        $this->urlRewriteService = $urlRewriteService;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->storelocatorConfig = $storelocatorConfig;
        $this->storesFactory = $storesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->uploaderPool = $uploaderPool;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($registry, $storesRepository,$storesFactory, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $stores = null;
        $data = $this->getRequest()->getPostValue();
        $filePath = $data["import"][0]["path"].$data["import"][0]["file"];
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data["import"][0]["path"] && $data["import"][0]["file"]) {
            
            try {
                $rawStoresData = $this->csvProcessor->getData($filePath);
                
                // first row of file represents headers
                $fileHeaders = $rawStoresData[0];
                $processedStoresData = $this->filterFileData($fileHeaders, $rawStoresData);
                foreach($processedStoresData as $individualStores) {
                    
                    $storesId = !empty($individualStores['stores_id']) ? $individualStores['stores_id'] : null;

                    if ($storesId) {
                        $stores = $this->storesRepository->getById((int)$storesId);
                    } else {
                        unset($individualStores['stores_id']);
                        $stores = $this->storesFactory->create();
                    }
                    $storeIds = $individualStores["store_id"] ?? $this->storeManager->getStore()->getId();

                    $individualStores['opening_hours'] = $this->formatOpeningHours($individualStores['opening_hours']); 
                    $individualStores['special_opening_hours'] = $this->formatSpecialOpeningHours($individualStores['special_opening_hours']); 
                    if(!empty($individualStores['products'])){
                        $products = explode(',',$individualStores['products']);
                        $products = array_flip($products);
                        $stores->setPostedProducts($products);
                    }
                    
                    $this->dataObjectHelper->populateWithArray($stores,$individualStores,StoresInterface::class);
                    $this->storesRepository->save($stores);

                }
    
                $this->messageManager->addSuccessMessage(__('Your file has been imported successfully'));
                
                $resultRedirect->setPath('storelocator/stores');                    
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($stores != null) {
                    $this->storeStoresDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $stores,
                            StoresInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('storelocator/stores/edit');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was an error importing the file'));
                if ($stores != null) {
                    $this->storeStoresDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $stores,
                            StoresInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('storelocator/stores/import');
            }
                
        } else {
            $this->messageManager->addError(__('Please upload a file'));
        }

        return $resultRedirect;
    }

    protected function formatOpeningHours($openingHours)
    {
        if(empty($openingHours)){
            return null;
        }
        $format = [];
        $openingHours = explode('|',$openingHours);
        if(empty($openingHours)){
            return null;
        }
        foreach($openingHours as $openingHour){
            list($dayOfWeek,$timeSlots) = explode('=',$openingHour);
            $timeSlots = explode(',',$timeSlots);
            if(!empty($timeSlots)){
                $format[$dayOfWeek] = [];
                foreach($timeSlots as $timeSlot) {
                    $format[$dayOfWeek][] = explode('->',$timeSlot);
                }
                $format[$dayOfWeek] = json_encode($format[$dayOfWeek]);
            }
        }
        //print_r($format);die;
        return $this->jsonHelper->jsonEncode($format);
    }

    protected function formatSpecialOpeningHours($specialOpeningHours)
    {
        if(empty($specialOpeningHours)){
            return null;
        }
        $format = [];
        $specialOpeningHours = explode('|',$specialOpeningHours);
        if(empty($specialOpeningHours)){
            return null;
        }
        $i=0;
        foreach($specialOpeningHours as $specialOpeningHour){
            list($day,$timeSlots) = explode('=',$specialOpeningHour);
            $timeSlots = explode(',',$timeSlots);
            if(!empty($timeSlots)){
                $special = [];
                foreach($timeSlots as $timeSlot) {
                    $special[] = explode('->',$timeSlot);
                }
                $format[(string)$i] = [
                    'date' => $day,
                    'opening_hours' => json_encode($special)
                ];
                $i++;
            }
            $format["__empty"] = null;
        }
        return $this->jsonHelper->jsonEncode($format);
    }
    /**
     * @param $storesData
     */
    public function storeStoresDataToSession($storesData)
    {
        $this->_getSession()->setEcomteckStoreLocatorStoresData($storesData);
    }

    /**
     * Filter data so that it will skip empty rows and headers
     *
     * @param   array $fileHeaders
     * @param   array $rawStoresData
     * @return  array
     */
    public function filterFileData(array $fileHeaders, array $rawStoresData)
    {
        $rowCount=0;
        $rawDataRows = [];
        
        foreach ($rawStoresData as $rowIndex => $dataRow) {
            
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rawStoresData[$rowIndex]);
                continue;
            }
            /* we take rows from [0] = > value to [website] = base */
            if ($rowIndex > 0) {
                foreach ($dataRow as $rowIndex => $dataRowNew) {
                    $rawDataRows[$rowCount][$fileHeaders[$rowIndex]] = $dataRowNew;
                }
            }
            $rowCount++;
        }
        return $rawDataRows;
    }
    
    
    /**
     * @param $type
     * @return Uploader
     * @throws \Exception
     */
    public function getUploader($type)
    {
        return $this->uploaderPool->getUploader($type);
    }

    /**
     * Saves the url rewrite for that specific store
     * @param $link string
     * @param $id int
     * @param $storeIds string
     * @return void
     */
    public function saveUrlRewrite($link, $id, $storeIds)
    {
        $moduleUrl = $this->storelocatorConfig->getModuleUrlSettings();
        $getCustomUrlRewrite = $moduleUrl . "/" . $link;
        $storesId = $moduleUrl . "-" . $id;

        $storeIds = explode(",", $storeIds);
        foreach ($storeIds as $storeId){

            $filterData = [
                UrlRewriteService::STORE_ID => $storeId,
                UrlRewriteService::REQUEST_PATH => $getCustomUrlRewrite,
                UrlRewriteService::ENTITY_ID => $id,

            ];

            // check if there is an entity with same url and same id
            $rewriteFinder = $this->urlFinder->findOneByData($filterData);

            // if there is then do nothing, otherwise proceed
            if ($rewriteFinder === null) {

                // check maybe there is an old url with this target path and delete it
                $filterDataOldUrl = [
                    UrlRewriteService::STORE_ID => $storeId,
                    UrlRewriteService::REQUEST_PATH => $getCustomUrlRewrite,
                ];
                $rewriteFinderOldUrl = $this->urlFinder->findOneByData($filterDataOldUrl);

                if ($rewriteFinderOldUrl !== null) {
                    $this->urlRewrite->load($rewriteFinderOldUrl->getUrlRewriteId())->delete();
                }

                // check maybe there is an old id with different url, in this case load the id and update the url
                $filterDataOldId = [
                    UrlRewriteService::STORE_ID => $storeId,
                    UrlRewriteService::ENTITY_TYPE => $storesId,
                    UrlRewriteService::ENTITY_ID => $id
                ];
                $rewriteFinderOldId = $this->urlFinder->findOneByData($filterDataOldId);

                if ($rewriteFinderOldId !== null) {
                    $this->urlRewriteFactory->create()->load($rewriteFinderOldId->getUrlRewriteId())
                        ->setRequestPath($getCustomUrlRewrite)
                        ->save();

                    continue;
                }

                // now we can save
                $this->urlRewriteFactory->create()
                    ->setStoreId($storeId)
                    ->setIdPath(rand(1, 100000))
                    ->setRequestPath($getCustomUrlRewrite)
                    ->setTargetPath("storelocator/view/index")
                    ->setEntityType($storesId)
                    ->setEntityId($id)
                    ->setIsAutogenerated(0)
                    ->save();
            }
        }
    }
    
}
