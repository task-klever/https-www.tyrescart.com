<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hdweb\WarrantyClaim\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Claim extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('hdweb_warrantyclaim_claim', 'claim_id');
    }
}

