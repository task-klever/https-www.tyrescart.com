<?php

namespace MGS\Blog\Model;

class Faq extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MGS\Blog\Model\Resource\Faq::class);
    }
}
