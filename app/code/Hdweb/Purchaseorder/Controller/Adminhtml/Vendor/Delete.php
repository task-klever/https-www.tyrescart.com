<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Vendor;

class Delete extends \Hdweb\Purchaseorder\Controller\Adminhtml\Vendor
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Vendor');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the vendor.'));
                $this->_redirect('purchaseorder/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete vendor right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('purchaseorder/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a vendor to delete.'));
        $this->_redirect('purchaseorder/*/');
    }
}
