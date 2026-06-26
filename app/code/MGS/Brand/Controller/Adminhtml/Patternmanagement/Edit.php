<?php

namespace MGS\Brand\Controller\Adminhtml\Patternmanagement;

use Magento\Backend\App\Action;

class Edit extends Action
{

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public $resultPageFactory;

	public $categoryFactory;

	public $registry;

	/**
	 * @var \Magento\Framework\Json\Helper\Data
	 */
	public $jsonHelper;

	/**
	 * Edit constructor.
	 * @param \Magento\Framework\Json\Helper\Data $jsonHelper
	 * @param \Magento\Framework\View\Result\PageFactory $pageFactory
	 * @param \Magento\Backend\App\Action\Context $context
	 */
	public function __construct(
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\Registry $registry,
		\MGS\Brand\Model\PatternmanagementFactory $categoryFactory,
		\Magento\Backend\App\Action\Context $context
	)
	{
		$this->jsonHelper         = $jsonHelper;
		$this->registry           = $registry;
		$this->resultPageFactory  = $pageFactory;
		$this->categoryFactory = $categoryFactory;

		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
		if ($this->getRequest()->isAjax()) {
//			$attCode = $this->getRequest()->getParam('attributeCode');
//			$options = $this->helper->getAttributeOptions($attCode);
//			if (!empty($options)) {
//				return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($options));
//			}
		}

		$cat = $this->categoryFactory->create();
		if ($id = $this->getRequest()->getParam('patternmanagement_id')) {
			$cat->load($id);
			if (!$cat->getId()) {
				$this->messageManager->addErrorMessage(__('The pattern doesnot exist.'));
				$this->_redirect('*/*/');

				return;
			}
		}

		//Set entered data if was error when we do save
		$data = $this->_session->getProductFormData(true);
		if (!empty($data)) {
			$cat->setData($data);
		}

		$this->registry->register('current_brand_patternmanagement', $cat);

		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set($cat->getId() ? $cat->getName() : __('New Pattern'));

		return $resultPage;
	}
}
