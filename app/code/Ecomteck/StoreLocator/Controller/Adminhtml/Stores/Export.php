<?php
/** Ecomteck_StoreLocator extension
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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;
use Ecomteck\StoreLocator\Controller\Adminhtml\Stores;
use Ecomteck\StoreLocator\Model\Uploader;
use Ecomteck\StoreLocator\Model\UploaderPool;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;



class Export extends Stores
{
    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    public $uploaderPool;
    
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

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
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->storesFactory = $storesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->uploaderPool = $uploaderPool;
        parent::__construct($registry, $storesRepository,$storesFactory, $resultPageFactory, $dateFilter, $context);
    }
    
    /**
     * Export data grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        try {
            
            $content = '';
            $content .= '"store_id",';
            $content .= '"name",';
            $content .= '"address",';
            $content .= '"city",';
            $content .= '"country",';
            $content .= '"postcode",';
            $content .= '"region",';
            $content .= '"email",';
            $content .= '"phone",';
            $content .= '"url_key",';
            $content .= '"image",';
            $content .= '"latitude",';
            $content .= '"longitude",';
            $content .= '"status",';
            $content .= '"updated_at",';
            $content .= '"created_at",';
            $content .= '"station",';
            $content .= '"description",';
            $content .= '"intro",';
            $content .= '"details_image",';
            $content .= '"distance",';
            $content .= '"external_link",';
            $content .= '"opening_hours",';
            $content .= '"special_opening_hours",';
            $content .= '"category",';
            $content .= '"products"';
            $content .= "\n";

            $fileName = 'storelocator_export.csv';
            $collection = $this->collectionFactory->create();
            
            foreach ($collection as $stores) {
                //die($stores->getSpecialOpeningHoursExport());
                $data = $stores->getData();
                array_shift($data); //skip the id
                $data['opening_hours'] = $stores->getOpeningHoursExport();
                $data['special_opening_hours'] = $stores->getSpecialOpeningHoursExport();
                if($products = $stores->getProductsPosition()){
                    $data['products'] = implode(',',array_keys($products));
                }
                //unset($data['opening_hours']);
                //unset($data['special_opening_hours']);
                $content .= implode(",", array_map([$this, 'addQuotationMarks'],$data));
                $content .= "\n";
            }

            return $this->fileFactory->create(
                $fileName,
                $content,
                DirectoryList::VAR_DIR
            );
            
            $this->messageManager->addSuccessMessage(__('You exported the file. It can be found in var folder or in browser downloads.'));
            $resultRedirect->setPath('storelocator/stores');
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem exporting the data'));
            $resultRedirect->setPath('storelocator/stores/export');
        }
        
        return $resultRedirect;

    }
    
     /**
     * Add quotes to fields
     * @param string
     * @return string
     */
    public function addQuotationMarks($row)
    {
        return sprintf('"%s"', $row);
    }
}
