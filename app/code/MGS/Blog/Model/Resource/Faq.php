<?php

namespace MGS\Blog\Model\Resource;

class Faq extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('mgs_blog_post_faq', 'id');
    }
}
