<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Vendor;

class Save extends \Hdweb\Purchaseorder\Controller\Adminhtml\Vendor
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Vendor');
                $data = $this->getRequest()->getPostValue();


                $inputFilter = new \Magento\Framework\Filter\FilterInput(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong vendor is specified.'));
                    }
                }
                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();

                $this->saveFitmentCharges($model->getId()); // save po vendor fitment charges

                $this->messageManager->addSuccess(__('You saved the vendor.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('purchaseorder/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('purchaseorder/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int) $this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('purchaseorder/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('purchaseorder/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the vendor data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('purchaseorder/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('purchaseorder/*/');
    }

    public function saveFitmentCharges($id)
    {
        $fitment = $this->getRequest()->getParam("fitment");
        if (count($fitment) > 0) {
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('po_vendor_fitment');
            foreach ($fitment as $key => $fitmentData) {
                if (!empty($fitmentData["sku"]) && !empty($fitmentData["vendor_price"])) {

                    $sql = "Select * FROM " . $tableName . ' WHERE sku="' . $fitmentData["sku"] . '" AND vendor_id=' . $id;
                    $result = $connection->fetchAll($sql);

                    if (count($result) > 0) {
                        // update data
                        $updateSql = "UPDATE " . $tableName . " SET vendor_price = " . $fitmentData["vendor_price"] . " WHERE sku='" . $fitmentData["sku"] . "' AND vendor_id=" . $id;
                        $connection->query($updateSql);
                    } else {
                        // store data
                        $fitmentPostData = array(
                            "vendor_id" => $id,
                            "sku" => $fitmentData["sku"],
                            "vendor_price" => $fitmentData["vendor_price"],
                            "status" => 1,
                        );
                        $povendorfitmentModel = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Povendorfitment');
                        $povendorfitmentModel->setData($fitmentPostData);

                        try {

                            $povendorfitmentModel->save();
                        } catch (Exception $e) {
                            $this->messageManager->addError($e->getMessage());
                        }
                    }

                }
            }
        }
    }
}
