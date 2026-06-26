<?php
namespace Hdweb\Rfc\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
	protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hdweb_Rfc::details');
        $resultPage->addBreadcrumb(__('Hdweb'), __('RFC'));
        $resultPage->addBreadcrumb(__('Rfc'), __('Details'));
        $resultPage->getConfig()->getTitle()->prepend(__('RFC Details'));
        return $resultPage;
    }

}