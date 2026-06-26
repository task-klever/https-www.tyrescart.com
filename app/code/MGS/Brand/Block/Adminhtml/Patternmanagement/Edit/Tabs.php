<?php

namespace MGS\Brand\Block\Adminhtml\Patternmanagement\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	protected function _construct()
	{
		parent::_construct();
		$this->setId('brand_patternmanagement_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(__('Pattern Management'));
	}
}