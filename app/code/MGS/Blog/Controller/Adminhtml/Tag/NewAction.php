<?php

namespace MGS\Blog\Controller\Adminhtml\Tag;

use MGS\Blog\Controller\Adminhtml\Blog;

class NewAction extends Blog
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Tags'));
        return $resultPage;
    }

    
}
