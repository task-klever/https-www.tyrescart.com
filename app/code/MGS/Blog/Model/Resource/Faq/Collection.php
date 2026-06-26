<?php

namespace MGS\Blog\Model\Resource\Faq;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \MGS\Blog\Model\Faq::class,
            \MGS\Blog\Model\Resource\Faq::class
        );
    }
}
