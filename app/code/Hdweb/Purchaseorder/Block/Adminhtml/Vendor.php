<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */
namespace Hdweb\Purchaseorder\Block\Adminhtml;

class Vendor extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'vendor';
        $this->_headerText = __('Vendor');
        $this->_addButtonLabel = __('Add New Vendor');
        parent::_construct();
    }
}
