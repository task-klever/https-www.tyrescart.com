<?php

namespace MGS\Brand\Block\Adminhtml\Patternmanagement\Edit\Tab;

class Patternmanagement extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Config\Model\Config\Source\Enabledisable
     */
    protected $_booleanOptions;
	
	/** @var \Magento\Cms\Model\Wysiwyg\Config  */
	protected $_wysiwygConfig;
	
	protected $eavConfig;

    /**
     * Patternmanagement constructor.
     *
     * @param \Magento\Config\Model\Config\Source\Enabledisable     $booleanOptions
     * @param \Magento\Backend\Block\Template\Context               $context
     * @param \Magento\Framework\Registry                           $registry
     * @param \Magento\Framework\Data\FormFactory                   $formFactory
     * @param \Magento\Store\Model\System\Store                     $systemStore
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Enabledisable $booleanOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
		\Magento\Eav\Model\Config $eavConfig,
		array $data = array()
    ) {
        $this->_booleanOptions = $booleanOptions;
        $this->_systemStore = $systemStore;
		$this->_wysiwygConfig = $wysiwygConfig;
		$this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \MGS\Brand\Model\Patternmanagement */
        $model = $this->_coreRegistry->registry('current_brand_patternmanagement');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('patternmanagement_');

        $fieldset = $form->addFieldset(
            'base_fieldset', array('legend' => __('Patternmanagement Information'))
        );

        if ($model->getPatternmanagementId()) {
            $fieldset->addField('patternmanagement_id', 'hidden', array('name' => 'patternmanagement_id'));
        }

        $fieldset->addField(
            'brand_id', 'select',
            ['name'     => 'brand_id', 'label' => __('Brand'), 'title' => __('Brand'),
             'required' => true,
			 'values' => $this->getAllBrandOptions()
			]
        );
		
		$fieldset->addField(
            'pattern_id', 'select',
            ['name'     => 'pattern_id', 'label' => __('Pattern'), 'title' => __('Pattern'),
             'required' => true,
			 'values' => $this->getAllPatternOptions()
			]
        );
		
		$fieldset->addField('image', 'image', [
				'name'  => 'image',
				'label' => __('Pattern Image'),
				'title' => __('Pattern Image'),
				'note'  => __('If empty, option visual image or default image from configuration will be used.')
			]
		);
		
		$fieldset->addField(
            'short_description', 'editor',
            [
			'name'  => 'short_description',
			'label' => __('Short Description'),
            'title' => __('Short Description'),
			'config' => $this->_wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false])
			]
        );
		
		$fieldset->addField(
            'description', 'editor',
            [
			'name'  => 'description',
			'label' => __('Description'),
            'title' => __('Description'),
			'config' => $this->_wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false])
			]
        );
		
		$fieldset->addField(
            'performance_description', 'editor',
            [
			'name'  => 'performance_description',
			'label' => __('Performance Description'),
            'title' => __('Performance Description'),
			'config' => $this->_wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false])
			]
        );
		
		$fieldset->addField(
            'dry', 'text',
            ['name'  => 'dry', 'label' => __('Dry'),
             'title' => __('Dry')]
        );
		
		$fieldset->addField(
            'wet', 'text',
            ['name'  => 'wet', 'label' => __('Wet'),
             'title' => __('Wet')]
        );
		
		$fieldset->addField(
            'sport', 'text',
            ['name'  => 'sport', 'label' => __('Sport'),
             'title' => __('Sport')]
        );
		
		$fieldset->addField(
            'comfort', 'text',
            ['name'  => 'comfort', 'label' => __('Comfort'),
             'title' => __('Comfort')]
        );
		
		$fieldset->addField(
            'mileage', 'text',
            ['name'  => 'mileage', 'label' => __('Mileage'),
             'title' => __('Mileage')]
        );
		
		$fieldset->addField(
            'youtube_video_link', 'text',
            ['name'  => 'youtube_video_link', 'label' => __('Youtube Video Link'),
             'title' => __('Youtube Video Link')]
        );
		
        $fieldset->addField(
            'url_key', 'text', ['name'  => 'url_key', 'label' => __('Url key'),
                                'title' => __('Url key')]
        );

        $fieldset->addField(
            'status', 'select', ['name'   => 'status', 'label' => __('Status'),
                                 'title'  => __('Status'),
                                 'values' => $this->_booleanOptions->toOptionArray(
                                 )]
        );


        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                //'multiselect',
                'select',
                [
                    'name' => 'store_id',
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
            'meta_title', 'text',
            ['name'  => 'meta_title', 'label' => __('Meta Title'),
             'title' => __('Meta Title')]
        );
        $fieldset->addField(
            'meta_keywords', 'text',
            ['name'  => 'meta_keywords', 'label' => __('Meta Keywords'),
             'title' => __('Meta Keywords')]
        );
        $fieldset->addField(
            'meta_description', 'textarea',
            ['name'  => 'meta_description', 'label' => __('Meta Description'),
             'title' => __('Meta Description')]
        );
        
        if (!$model->getId()) {
            $model->addData(
                ['status' => 1, 'store_ids' => '0']
            );
        }

        $savedData = $model->getData();
        $form->setValues($savedData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Pattern Management');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Pattern Management');
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
	
	public function getAllBrandOptions()
	{
		$attributeCode = "brand";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$options = $attribute->getSource()->getAllOptions();
		$arr = [];
		foreach ($options as $option) {
		    if ($option['value'] > 0) {
		        $arr[] = $option;
		    }
		}
		return $arr;
	}
	
	public function getAllPatternOptions()
	{
        
		$attributeCode = "pattern";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$options = $attribute->getSource()->getAllOptions();
		$arr = [];
		foreach ($options as $option) {
		    if ($option['value'] > 0) {
		        $arr[] = $option;
		    }
		}
		return $arr;
	}
}
