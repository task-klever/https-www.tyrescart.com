<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

namespace Hdweb\Purchaseorder\Model\Resource\Vendor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hdweb\Purchaseorder\Model\Vendor', 'Hdweb\Purchaseorder\Model\Resource\Vendor');
    }
}
