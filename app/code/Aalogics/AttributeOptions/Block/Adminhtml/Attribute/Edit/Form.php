<?php

namespace Aalogics\AttributeOptions\Block\Adminhtml\Attribute\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'    => 'add_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setUseContainer(true);
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General')]
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name'      => 'attribute_code',
                'label'     => __('Attribute Code'),
            ]
        );
        $fieldset->addField(
            'attribute_values',
            'text',
            [
                'name'      => 'attribute_values',
                'label'     => __('Attribute Values'),
                'placeholder'=> 'comma-separated (Red,Green,Blue)',
                'after_element_html' => '<br><br><button type="submit">Save</button>'
            ]
        );
        // $fieldset->addField(
        //     'file',
        //     'file', 
        //     array(
        //       'label'     => 'Upload csv',
        //       'name'  => 'csvupload[]',
        //       'value'  => 'Upload',
        //       'multiple' => 'multiple',
        //       'multiple'  => true,
        //       'tabindex' => 1
        //     )
        // );
        $this->setForm($form);

        return parent::_prepareForm();
    }
}