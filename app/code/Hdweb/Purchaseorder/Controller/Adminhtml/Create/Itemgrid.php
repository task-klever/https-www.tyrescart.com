<?php

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Itemgrid extends \Magento\Backend\App\Action
{

    protected $resultPagee;
    protected $resultPageFactory;

    public function __construct(
        Context $context, PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Hdweb'), __('Hdweb'));
        $resultPage->getConfig()->getTitle()->prepend(__('Purchaseorder Item detail'));

        return $resultPage;
    }

}
