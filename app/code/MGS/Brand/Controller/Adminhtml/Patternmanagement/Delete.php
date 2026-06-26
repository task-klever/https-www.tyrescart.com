<?php

namespace MGS\Brand\Controller\Adminhtml\Patternmanagement;

class Delete extends \Magento\Backend\App\Action
{
	/**
	 * Delete page item
	 */
	public function execute()
	{
		$id = $this->getRequest()->getParam('patternmanagement_id');
		try {
			$cat = $this->_objectManager->create('MGS\Brand\Model\Patternmanagement')->load($id);
			if ($cat && $cat->getId()) {
				$cat->delete();
				$this->messageManager->addSuccessMessage(__('Delete successfully !'));
			} else {
				$this->messageManager->addErrorMessage(__('Cannot find category to delete.'));
			}
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
		}

		$this->_redirect('*/*/');
	}
}
