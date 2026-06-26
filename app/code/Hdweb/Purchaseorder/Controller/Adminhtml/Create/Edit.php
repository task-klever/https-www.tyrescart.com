<?php
 
namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
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
  
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Purchase Order'));

        return $resultPage;
    }

}