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
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;
use Ecomteck\StoreLocator\Controller\Adminhtml\Stores;
use Ecomteck\StoreLocator\Model\Uploader;
use Ecomteck\StoreLocator\Model\UploaderPool;
use Magento\UrlRewrite\Model\UrlRewrite as BaseUrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ecomteck\StoreLocator\Model\Config as StoreLocatorConfig;

class Save extends Stores
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

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /**
     * @param Registry $registry
     * @param StoresRepositoryInterface $storesRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param StoresInterfaceFactory $storesFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param UploaderPool $uploaderPool
     */
    public function __construct(
        Registry $registry,
        StoresRepositoryInterface $storesRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        BaseUrlRewrite $urlRewrite,
        UrlRewriteService $urlRewriteService,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StoresInterfaceFactory $storesFactory,
        StoreLocatorConfig $storelocatorConfig,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
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
        /** @var \Ecomteck\StoreLocator\Api\Data\StoresInterface $stores */
        $stores = null;
        $data = $this->getRequest()->getPostValue();

        $id = !empty($data['stores_id']) ? $data['stores_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($id) {
                $stores = $this->storesRepository->getById((int)$id);
            } else {
                unset($data['stores_id']);
                $stores = $this->storesFactory->create();
            }
            $image = $this->getUploader('image')->uploadFileAndGetName('image', $data);
            $data['image'] = $image;

            $image_1 = $this->getUploader('image')->uploadFileAndGetName('image_1', $data);
            $data['image_1'] = $image_1;
            $stores['image_1'] = $image_1;

            $image_2 = $this->getUploader('image')->uploadFileAndGetName('image_2', $data);
            $data['image_2'] = $image_2;
            $stores['image_2'] = $image_2;

            $image_3 = $this->getUploader('image')->uploadFileAndGetName('image_3', $data);
            $data['image_3'] = $image_3;
            $stores['image_3'] = $image_3;

            $image_4 = $this->getUploader('image')->uploadFileAndGetName('image_4', $data);
            $data['image_4'] = $image_4;
            $stores['image_4'] = $image_4;

            $image_5 = $this->getUploader('image')->uploadFileAndGetName('image_5', $data);
            $data['image_5'] = $image_5;
            $stores['image_5'] = $image_5;


            $details_image = $this->getUploader('image')->uploadFileAndGetName('details_image', $data);
            $data['details_image'] = $details_image;

            if(!empty($data['store_id']) && is_array($data['store_id'])) {
                if(in_array('0',$data['store_id'])){
                    $data['store_id'] = '0';
                }
                else{
                    $data['store_id'] = implode(",", $data['store_id']);
                }
            }
            if(isset($data['opening_hours']) && is_array($data['opening_hours'])){
                
                $data['opening_hours'] = $this->jsonHelper->jsonEncode($data['opening_hours']);
                
            }

            if(isset($data['special_opening_hours']) && is_array($data['special_opening_hours'])){
                $data['special_opening_hours'] = $this->jsonHelper->jsonEncode($data['special_opening_hours']);
            }

            $storeId = (isset($data["store_id"]) && $data["store_id"]) ?$data["store_id"]: $this->storeManager->getStore()->getId();

            // Check for additional slider products
            if (isset($data['store_products']) && is_string($data['store_products']))
            {
                $products = json_decode($data['store_products'], true);
                $stores->setPostedProducts($products);
                $stores->unsetData('store_products');
            }

            $this->dataObjectHelper->populateWithArray($stores, $data, StoresInterface::class);

            $this->storesRepository->save($stores);

            $this->messageManager->addSuccessMessage(__('You saved the store'));
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('storelocator/stores/edit', ['stores_id' => $stores->getId()]);
            } else {
                $resultRedirect->setPath('storelocator/stores');
            }
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
            $resultRedirect->setPath('storelocator/stores/edit', ['stores_id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem saving the store'));
            if ($stores != null) {
                $this->storeStoresDataToSession(
                    $this->dataObjectProcessor->buildOutputDataArray(
                        $stores,
                        StoresInterface::class
                    )
                );
            }
            $resultRedirect->setPath('storelocator/stores/edit', ['stores_id' => $id]);
        }
        return $resultRedirect;
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
     * @param $storesData
     */
    public function storeStoresDataToSession($storesData)
    {
        $this->_getSession()->setEcomteckStoreLocatorStoresData($storesData);
    }
}
