<?php
namespace Hdweb\WarrantyClaim\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Comment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('hdweb_warranty_claim_comment', 'id'); // Table name and primary key
    }
}
