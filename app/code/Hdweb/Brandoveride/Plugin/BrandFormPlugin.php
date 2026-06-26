<?php
namespace Hdweb\Brandoveride\Plugin;

class BrandFormPlugin
{
    public function beforePrepareForm(\MGS\Brand\Block\Adminhtml\Brand\Edit\Tab\Main $subject)
    {
        $form = $subject->getForm();
        if (!$form) {
            return null; // form not ready yet
        }

        $fieldset = $form->getElement('general');
        if (!$fieldset) {
            $fieldset = $form->addFieldset('general', ['legend' => __('General Information')]);
        }

        $fieldset->addField(
            'tab1_title',
            'text',
            ['name' => 'brand[tab1_title]', 'label' => __('Tab1 Title'), 'title' => __('Tab1 Title'), 'required' => false]
        );
         $fieldset->addField(
            'tab1_description',
            'editor',
            ['name' => 'brand[tab1_description]', 'label' => __('Tab1 Description'), 'title' => __('Tab1 Description'), 'required' => false, 'config' => $wysiwygConfig]
        ); 

        $fieldset->addField(
            'tab2_title',
            'text',
            ['name' => 'brand[tab2_title]', 'label' => __('Tab2 Title'), 'title' => __('Tab2 Title'), 'required' => false]
        );
         $fieldset->addField(
            'tab2_description',
            'editor',
            ['name' => 'brand[tab2_description]', 'label' => __('Tab2 Description'), 'title' => __('Tab2 Description'), 'required' => false, 'config' => $wysiwygConfig]
        ); 


        
        $fieldset->addField(
            'tab3_title',
            'text',
            ['name' => 'brand[tab3_title]', 'label' => __('Tab3 Title'), 'title' => __('Tab3 Title'), 'required' => false]
        );
         $fieldset->addField(
            'tab3_description',
            'editor',
            ['name' => 'brand[tab3_description]', 'label' => __('Tab3 Description'), 'title' => __('Tab3 Description'), 'required' => false, 'config' => $wysiwygConfig]
        ); 

          
        $fieldset->addField(
            'tab4_title',
            'text',
            ['name' => 'brand[tab4_title]', 'label' => __('Tab4 Title'), 'title' => __('Tab4 Title'), 'required' => false]
        );
         $fieldset->addField(
            'tab4_description',
            'editor',
            ['name' => 'brand[tab4_description]', 'label' => __('Tab4 Description'), 'title' => __('Tab4 Description'), 'required' => false, 'config' => $wysiwygConfig]
        ); 


          $fieldset->addField(
            'tab5_title',
            'text',
            ['name' => 'brand[tab5_title]', 'label' => __('Tab5 Title'), 'title' => __('Tab5 Title'), 'required' => false]
        );
         $fieldset->addField(
            'tab5_description',
            'editor',
            ['name' => 'brand[tab5_description]', 'label' => __('Tab5 Description'), 'title' => __('Tab5 Description'), 'required' => false, 'config' => $wysiwygConfig]
        ); 

         $fieldset->addField(
            'topbanner_image',
            'image',
            ['name' => 'topbanner_image', 'label' => __('Top Banner Image'), 'title' => __('Top Banner Image'), 'required' => false]
        );

         $fieldset->addField(
            'bottombanner_image',
            'image',
            ['name' => 'bottombanner_image', 'label' => __('Bottom Banner Image'), 'title' => __('Bottom Banner Image'), 'required' => false]
        );
    }
}