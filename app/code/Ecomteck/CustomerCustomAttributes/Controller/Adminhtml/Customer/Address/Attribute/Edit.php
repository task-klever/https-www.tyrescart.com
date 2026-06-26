<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Controller\Adminhtml\Customer\Address\Attribute;

class Edit extends \Ecomteck\CustomerCustomAttributes\Controller\Adminhtml\Customer\Address\Attribute
{
    /**
     * Edit attribute action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        /* @var $attributeObject \Magento\Customer\Model\Attribute */
        $attributeObject = $this->_initAttribute()->setEntityTypeId($this->_getEntityType()->getId());

        if ($attributeId) {
            $attributeObject->load($attributeId);
            if (!$attributeObject->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                $this->messageManager->addError(__('You cannot edit this attribute.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // restore attribute data
        $attributeData = $this->_getSession()->getAttributeData(true);
        if (!empty($attributeData)) {
            $attributeObject->setData($attributeData);
        }

        // register attribute object
        $this->_coreRegistry->register('entity_attribute', $attributeObject);

        $label = $attributeObject->getId() ? __(
            'Edit Customer Address Attribute'
        ) : __(
            'New Customer Address Attribute'
        );

        $this->_initAction()->_addBreadcrumb($label, $label);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Customer Address Attributes'));
        $attributeId ? $this->_view->getPage()->getConfig()->getTitle()->prepend($attributeObject->getFrontendLabel())
            : $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Customer Address Attribute'));
        $this->_view->renderLayout();
    }
}
