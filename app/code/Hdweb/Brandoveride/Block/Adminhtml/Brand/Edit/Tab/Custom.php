<?php
namespace Hdweb\Brandoveride\Block\Adminhtml\Brand\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use MGS\Brand\Model\System\Config\Status;
use MGS\Brand\Model\System\Config\Yesno;

class Custom extends Generic implements TabInterface
{

     protected $_wysiwygConfig;
    protected $_status;
    protected $_yesno;
    protected $_systemStore;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $status,
        Yesno $yesno,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_status = $status;
        $this->_yesno = $yesno;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('custom_fieldset', ['legend' => __('Custom Fields')]);

         
        //$form = $this->_formFactory->create();
       // $form->setHtmlIdPrefix('brand_general_');
       // $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General')]);
        $model = $this->_coreRegistry->registry('current_brand');
        if ($model->getId()) {
            $fieldset->addField('brand_id', 'hidden', ['name' => 'brand_id']);
        }
        //echo  "<pre>";
        //print_r($model->getData());

         $wysiwygConfig = $this->_wysiwygConfig->getConfig();
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


        
       /* $fieldset->addField(
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
        ); */

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
        
        

        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    public function getTabLabel() { return __('Custom Tab'); }
    public function getTabTitle() { return __('Custom Tab'); }
    public function canShowTab() { return true; }
    public function isHidden() { return false; }
}