<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Model\ResourceModel\Claim;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'claim_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Hdweb\WarrantyClaim\Model\Claim::class,
            \Hdweb\WarrantyClaim\Model\ResourceModel\Claim::class
        );
    }
}

