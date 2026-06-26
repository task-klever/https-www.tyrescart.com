<?php
/**
 * Copyright © 2015 Hdweb. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Hdweb\Purchaseorder\Block\Adminhtml\Vendor\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;



class Main extends Generic implements TabInterface
{

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Vendor Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Vendor Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_hdweb_vendor');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('vendor_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Vendor Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Vendor Name'), 'title' => __('Vendor Name'), 'required' => true]
        );
        $fieldset->addField(
            'contact_person',
            'text',
            ['name' => 'contact_person', 'label' => __('Contact Person'), 'title' => __('Contact Person'), 'required' => true]
        );
        $fieldset->addField(
            'email',
            'text',
            ['name' => 'email', 'label' => __('Email'), 'title' => __('Email'), 'class' => 'validate-email', 'required' => true]
        );
        $fieldset->addField(
            'email_copy',
            'text',
            ['name' => 'email_copy', 'label' => __('Email Copy'), 'title' => __('Email Copy'), 'class' => '', 'required' => false]
        );
        $fieldset->addField(
            'phone',
            'text',
            ['name' => 'phone', 'label' => __('Phone'), 'title' => __('Phone'), 'class' => 'validate-number', 'required' => true]
        );
        $fieldset->addField(
            'phone2',
            'text',
            ['name' => 'phone2', 'label' => __('Another Phone'), 'title' => __('Another Phone'), 'class' => 'validate-number', 'required' => true]
        );
        $fieldset->addField(
            'whatsapp_number',
            'text',
            ['name' => 'whatsapp_number', 'label' => __('Whatsapp Number'), 'title' => __('Whatsapp Number'), 'class' => 'validate-number validate-length maximum-length-9 minimum-length-9', 'required' => true]
        );
        $fieldset->addField(
            'address',
            'text',
            ['name' => 'address', 'label' => __('Address'), 'title' => __('Address'), 'required' => true]
        );
        $fieldset->addField(
            'city',
            'text',
            ['name' => 'city', 'label' => __('City'), 'title' => __('City'), 'required' => true]
        );
        $fieldset->addField(
            'code',
            'text',
            ['name' => 'code', 'label' => __('Code'), 'title' => __('Code'), 'required' => true]
        );

        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
        $installerList = $objectManager->get('Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection');
        $installer = $installerList->toInstallerOptionArray();
        $fieldset->addField(
            'pickup_store', 'select', [
            'name' => 'pickup_store',
            'label' => __('Installer Name'),
            'title' => __('Installer Name'),
            'values' => $installer
                ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => [
                    0 => __('Inactive'),
                    1 => __('Active'),
                ]
            ]
        );
        if (!$model->getId()) {
            $model->setData('status', 1);
        }
        $fieldset->addField(
            'vatApplicable',
            'select',
            [
                'label' => __('Vat Applicable'),
                'title' => __('Vat Applicable'),
                'name' => 'vatApplicable',
                'required' => true,
                'options' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ]
            ]
        );
        if (!$model->getId()) {
            $model->setData('vatApplicable', 1);
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
