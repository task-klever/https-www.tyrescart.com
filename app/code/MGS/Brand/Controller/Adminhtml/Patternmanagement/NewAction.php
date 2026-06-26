<?php

namespace MGS\Brand\Controller\Adminhtml\Patternmanagement;

use Magento\Backend\App\Action;

class NewAction extends Action
{
	/**
	 * Forward to edit page
	 */
	public function execute()
	{
		$this->_forward('edit');
	}
}
