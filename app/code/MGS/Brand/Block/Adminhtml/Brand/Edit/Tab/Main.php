<?php

namespace MGS\Brand\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use MGS\Brand\Model\System\Config\Status;
use MGS\Brand\Model\System\Config\Yesno;


class Main extends Generic implements TabInterface
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

    public function getTabLabel()
    {
        return __('General');
    }

    public function getTabTitle()
    {
        return __('General');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_brand');
        // echo  "<pre>";
        // print_r($model->getData());
        
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('brand_general_');
        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldset->addField('brand_id', 'hidden', ['name' => 'brand_id']);
        }
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'brand[name]', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );
        $fieldset->addField(
            'url_key',
            'text',
            ['name' => 'brand[url_key]', 'label' => __('URL Key'), 'title' => __('URL Key'), 'required' => false, 'class' => 'validate-identifier']
        );
        $fieldset->addField(
            'small_image',
            'image',
            ['name' => 'small_image', 'label' => __('Small Image'), 'title' => __('Small Image'), 'required' => false]
        );
        $fieldset->addField(
            'image',
            'image',
            ['name' => 'image', 'label' => __('Image'), 'title' => __('Image'), 'required' => false]
        );
        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $fieldset->addField(
            'description',
            'editor',
            ['name' => 'brand[description]', 'label' => __('Description'), 'title' => __('Description'), 'required' => false, 'config' => $wysiwygConfig]
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }
        $fieldset->addField(
            'status',
            'select',
            ['name' => 'brand[status]', 'label' => __('Status'), 'title' => __('Status'), 'options' => $this->_status->toOptionArray()]
        );
        $fieldset->addField(
            'is_featured',
            'select',
            ['name' => 'brand[is_featured]', 'label' => __('Featured Brand'), 'title' => __('Featured Brand'), 'options' => $this->_yesno->toOptionArray()]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            ['name' => 'brand[sort_order]', 'label' => __('Sort Order'), 'title' => __('Sort Order'), 'required' => false]
        );
        $fieldset->addField(
            'brand_category',
            'select',
            ['name' => 'brand[brand_category]', 'label' => __('Brand Category'), 'title' => __('Brand Category'), 'options' => $this->getAllPartsCategories()]
        );
        $fieldset->addField(
            'brand_segment',
            'select',
            [
                'name' => 'brand[brand_segment]',
                'label' => __('Brand Segment'),
                'title' => __('Brand Segment'),
                'required' => false,
                'options' => [
                    '' => __('-- Select --'),
                    'top_quality' => __('Top Quality'),
                    'top_budget' => __('Top Budget'),
                    'top_premium' => __('Top Premium'),
                ]
            ]
        );

        

         /*$fieldset->addField(
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
        */

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getAllPartsCategories()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributeCode = 'parts_category';
        $eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
        $attributeRepository = $objectManager->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        $optionsArray = [];
        if ($attribute && $attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
            foreach ($options as $option) {
                $optionsArray[$option['label']] = $option['label'];
            }
        }
        return $optionsArray;
    }
}
