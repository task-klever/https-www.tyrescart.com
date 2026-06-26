<?php
 
namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Grid extends \Magento\Backend\App\Action
{
     
    protected $resultPagee;

    protected $resultPageFactory;

   
    public function __construct(
    Context $context, PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

   
    public function execute() {
    
        $resultPage = $this->resultPageFactory->create();
//        $resultPage->setActiveMenu('Hdweb_Purchaseorder::areamaster');
        $resultPage->addBreadcrumb(__('Hdweb'), __('Hdweb'));
        $resultPage->getConfig()->getTitle()->prepend(__('Purchase Order Summary'));

        return $resultPage;
    }

}