<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Vendor;

class Edit extends \Hdweb\Purchaseorder\Controller\Adminhtml\Vendor
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Vendor');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This vendor no longer exists.'));
                $this->_redirect('purchaseorder/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_hdweb_vendor', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('vendor_vendor_edit');
        $this->_view->renderLayout();
    }
}
