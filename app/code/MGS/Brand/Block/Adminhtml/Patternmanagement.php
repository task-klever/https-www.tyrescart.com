<?php

namespace MGS\Brand\Block\Adminhtml;

class Patternmanagement extends \Magento\Backend\Block\Widget\Grid\Container
{
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_controller     = 'adminhtml_patternmanagement';/*block grid.php directory*/
		$this->_blockGroup     = 'MGS_Brand';
		$this->_headerText     = __('Brand Patternmanagement');
		$this->_addButtonLabel = __('Add New Pattern');

		parent::_construct();
	}
}
