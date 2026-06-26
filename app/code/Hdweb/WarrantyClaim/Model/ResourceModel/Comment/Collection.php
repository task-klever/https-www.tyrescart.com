<?php
namespace Hdweb\WarrantyClaim\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Hdweb\WarrantyClaim\Model\Comment::class,
            \Hdweb\WarrantyClaim\Model\ResourceModel\Comment::class
        );
    }
}
