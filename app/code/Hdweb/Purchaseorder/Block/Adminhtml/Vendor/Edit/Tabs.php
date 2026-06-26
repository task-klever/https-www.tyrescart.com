<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */
namespace Hdweb\Purchaseorder\Block\Adminhtml\Vendor\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('hdweb_vendor_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Vendor'));
    }
}
