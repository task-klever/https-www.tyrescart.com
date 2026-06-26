<?php
namespace Hdweb\Brandoveride\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;

class Main extends \MGS\Brand\Block\Adminhtml\Brand\Edit\Tab\Main
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        
        // Main fieldset
        $fieldset = $form->addFieldset('general', ['legend' => __('General Information')]);

        // Call parent fields
        parent::_prepareForm();

        // --- Custom tab fields ---
        for ($i = 1; $i <= 5; $i++) {
            $fieldset->addField(
                "tab{$i}_title",
                'text',
                [
                    'name'  => "tab{$i}_title",
                    'label' => __("NewTab {$i} Title"),
                    'title' => __("NewTab {$i} Title"),
                ]
            );

            $fieldset->addField(
                "tab{$i}_description",
                'textarea',
                [
                    'name'  => "tab{$i}_description",
                    'label' => __("NewTab {$i} Description"),
                    'title' => __("NewTab {$i} Description"),
                ]
            );
        }

        // Top banner image
        $fieldset->addField(
            'topbanner_image',
            'image',
            [
                'name'  => 'topbanner_image',
                'label' => __('NewTabTop Banner Image'),
                'title' => __('Top Banner Image'),
                'note'  => 'Allowed file types: jpg, jpeg, png, gif',
            ]
        );

        // Bottom banner image
        $fieldset->addField(
            'bottombanner_image',
            'image',
            [
                'name'  => 'bottombanner_image',
                'label' => __('NewTabBottom Banner Image'),
                'title' => __('Bottom Banner Image'),
                'note'  => 'Allowed file types: jpg, jpeg, png, gif',
            ]
        );

        // Set form values
        $model = $this->_coreRegistry->registry('brand');
        if ($model) {
            $form->setValues($model->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}