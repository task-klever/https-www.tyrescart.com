<?php

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;


use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Io\File as FileIo;

class UpdateInvoice extends \Magento\Backend\App\Action
{

    protected $resultPagee;
    protected $purchaseorder;
    protected $purchaseorderitem;
    protected $povendor;
    protected $order;
    protected $scopeConfig;
    protected $productRepository;
    protected $pricehelper;
    protected $_filesystem;
    protected $fileFactory;
    protected $authSession;
    protected $orderInterfaceFactory;
    protected $orderItemFactory;
    protected $pohelper;
    private $objectManager;
    protected $addressConfig;
    protected $ecomtechStoreLocator;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $storeManager;
    protected $countryModel;

    protected $resultPageFactory;
    protected $y;
    protected $poModel;
    protected $filesystem;
    protected $fileDriver;
    protected $messageManager;
    protected $uploaderFactory;
    protected $fileIo;

    public function __construct(
        Context $context, PageFactory $resultPageFactory,
        \Hdweb\Purchaseorder\Model\PurchaseorderFactory $purchaseorder,
        \Hdweb\Purchaseorder\Model\PurchaseorderitemFactory $purchaseorderitem,
        \Hdweb\Purchaseorder\Model\Povendor $povendor,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricehelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        Session $authSession,
        \Hdweb\Purchaseorder\Helper\Data $pohelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Ecomteck\StoreLocator\Model\Stores $ecomtechStoreLocator,
        \Hdweb\Core\Model\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Country $countryModel,
        \Hdweb\Purchaseorder\Model\Purchaseorder $poModel,
        File $fileDriver,
        ManagerInterface $messageManager,
        UploaderFactory $uploaderFactory,
        FileIo $fileIo

    ) {
        parent::__construct($context);
        $this->resultPageFactory     = $resultPageFactory;
        $this->purchaseorder         = $purchaseorder;
        $this->purchaseorderitem     = $purchaseorderitem;
        $this->povendor              = $povendor;
        $this->order                 = $order;
        $this->scopeConfig           = $scopeConfig;
        $this->productRepository     = $productRepository;
        $this->pricehelper           = $pricehelper;
        $this->filesystem = $filesystem;
        $this->fileFactory           = $fileFactory;
        $this->authSession           = $authSession;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderItemFactory      = $orderItemFactory;
        $this->pohelper              = $pohelper;
        $this->objectManager = $objectmanager;
        $this->addressConfig = $addressConfig;
        $this->ecomtechStoreLocator = $ecomtechStoreLocator;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->countryModel = $countryModel;
        $this->poModel = $poModel;
        $this->fileDriver = $fileDriver;
        $this->messageManager = $messageManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileIo = $fileIo;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if (!$data) {
            $this->messageManager->addErrorMessage(__('No data to save.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $poData = $this->poModel->load($data['poid']);
            
            if (!$poData->getId()) {
                $this->messageManager->addErrorMessage(__('This purchase order no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            // Handle file upload
            if (isset($_FILES['invoice_upload']) && $_FILES['invoice_upload']['name']) {
                $uploader = $this->uploaderFactory->create(['fileId' => 'invoice_upload']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('po/invoices');

                // Build new filename
                $originalName = $_FILES['invoice_upload']['name'];
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $name = pathinfo($originalName, PATHINFO_FILENAME);
                $timestamp = time();
                $newFileName = $name . '-' . $timestamp . '.' . $ext;

                $result = $uploader->save($destinationPath, $newFileName);

                if ($result['file']) {
                    $data['invoice_upload'] = 'po/invoices/' . $result['file'];

                    // Remove old file if exists
                    if ($poData->getInvoiceUpload()) {
                        $oldFile = $mediaDirectory->getAbsolutePath($poData->getInvoiceUpload());
                        if ($this->fileDriver->isExists($oldFile)) {
                            $this->fileDriver->deleteFile($oldFile);
                        }
                    }
                }
            } elseif (isset($data['existing_invoice_upload'])) {
                $data['invoice_upload'] = $data['existing_invoice_upload'];
            } else {
                $data['invoice_upload'] = $poData->getInvoiceUpload();
            }

            // Format dates
            $data['invoice_date'] = !empty($data['invoice_date']) 
                ? date('Y-m-d H:i:s', strtotime($data['invoice_date'])) 
                : null;
            $data['invoice_due_date'] = !empty($data['invoice_due_date']) 
                ? date('Y-m-d H:i:s', strtotime($data['invoice_due_date'])) 
                : null;
            $data['payment_date'] = !empty($data['payment_date']) 
                ? date('Y-m-d H:i:s', strtotime($data['payment_date'])) 
                : null;

            // Save data
            $poData->addData([
                'invoice_number' => $data['invoice_number'],
                'invoice_date' => $data['invoice_date'],
                'invoice_due_date' => $data['invoice_due_date'],
                'payment_date' => $data['payment_date'],
                'payemnt_term_days' => $data['payemnt_term_days'] ?? null,
                'invoice_status' => $data['invoice_status'] ?? null,
                'invoice_remarks' => $data['invoice_remarks'] ?? null,
                'invoice_upload' => $data['invoice_upload'] ?? null
            ]);
            
            $poData->save();

            $this->messageManager->addSuccessMessage(__('Invoice details have been saved.'));
            return $resultRedirect->setPath('*/*/edit', ['po_id' => $data['poid']]);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving invoice details: %1', $e->getMessage()));
            return $resultRedirect->setPath('*/*/edit', ['po_id' => $data['poid']]);
        }
    }
}
