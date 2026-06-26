<?php

namespace MGS\Brand\Controller\Adminhtml\Patternmanagement;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @var \Magento\Backend\Model\View\Result\Page
	 */
	protected $resultPage;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);

		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
		$this->resultPage = $this->resultPageFactory->create();
		$this->resultPage->setActiveMenu('MGS_Brand::patternmanagement');
		$this->resultPage->getConfig()->getTitle()->prepend((__('Pattern Management')));

		return $this->resultPage;
	}

	/**
	 * Check admin permissions
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('MGS_Brand::patternmanagement');
	}
}
