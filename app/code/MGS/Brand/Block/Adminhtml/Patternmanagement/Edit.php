<?php

namespace MGS\Brand\Block\Adminhtml\Patternmanagement;

/**
 * CMS block edit form container
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	public $_coreRegistry;

	/**
	 * constructor
	 *
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Backend\Block\Widget\Context $context
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Backend\Block\Widget\Context $context,
		array $data = []
	)
	{
		$this->_coreRegistry = $coreRegistry;
		parent::__construct($context, $data);
	}

	protected function _construct()
	{
		$this->_objectId   = 'patternmanagement_id';
		$this->_blockGroup = 'MGS_Brand';
		$this->_controller = 'adminhtml_patternmanagement';

		parent::_construct();

		$this->buttonList->update('save', 'label', __('Save Pattern'));
		$this->buttonList->update('delete', 'label', __('Delete Pattern'));

		$this->buttonList->add(
			'saveandcontinue',
			array(
				'label'          => __('Save and Continue Edit'),
				'class'          => 'save',
				'data_attribute' => array(
					'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
				)
			),
			-100
		);
	}

	/**
	 * Get edit form container header text
	 *
	 * @return string
	 */
	public function getHeaderText()
	{
		if ($this->_coreRegistry->registry('current_brand_patternmanagement')->getId()) {
			return __("Edit Pattern '%1'", $this->escapeHtml($this->_coreRegistry->registry('current_brand_patternmanagement')->getName()));
		} else {
			return __('New Pattern');
		}
	}
}
