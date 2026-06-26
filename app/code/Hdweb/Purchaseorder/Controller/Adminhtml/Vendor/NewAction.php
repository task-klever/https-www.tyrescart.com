<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Vendor;

class NewAction extends \Hdweb\Purchaseorder\Controller\Adminhtml\Vendor
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
