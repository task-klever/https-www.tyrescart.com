<?php

namespace MGS\Blog\Controller\Adminhtml\Tag;

use MGS\Blog\Controller\Adminhtml\Blog;

class Edit extends Blog
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('tag_id');
        $model = $this->_objectManager->create('MGS\Blog\Model\Tag');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This tag no longer exists.'));
                $this->_redirect('blog/tag/index');
                return;
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_tag', $model);
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Tag') : __('Add New Tag'),
            $id ? __('Edit Tag') : __('Add New Tag')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Blog'));
        $this->_view->getPage()->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('Add New Tag'));
        $this->_view->getLayout()->getBlock('tag_edit');
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::edit_tag');
    }
}
