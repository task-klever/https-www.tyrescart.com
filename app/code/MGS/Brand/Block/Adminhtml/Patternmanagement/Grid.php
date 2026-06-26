<?php

namespace MGS\Brand\Block\Adminhtml\Patternmanagement;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
	 * @var \MGS\Brand\Model\ResourceModel\Patternmanagement\Collection
	 */
	protected $_collectionFactory;

	/** @var \Magento\Config\Model\Config\Source\Enabledisable */
	protected $_booleanOptions;

	/** @var \Magento\Store\Model\System\Store */
	protected $_systemStore;

	/**
	 * Grid constructor.
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Helper\Data $backendHelper
	 * @param \Magento\Config\Model\Config\Source\Enabledisable $booleanOptions
	 * @param \Magento\Store\Model\System\Store $systemStore
	 * @param \MGS\Brand\Model\ResourceModel\Patternmanagement\Collection $collectionFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Config\Model\Config\Source\Enabledisable $booleanOptions,
		\Magento\Store\Model\System\Store $systemStore,
		\MGS\Brand\Model\ResourceModel\Patternmanagement\Collection $collectionFactory,
		array $data = []
	)
	{
		$this->_collectionFactory = $collectionFactory;
		$this->_booleanOptions    = $booleanOptions;
		$this->_systemStore       = $systemStore;

		parent::__construct($context, $backendHelper, $data);
	}

	/**
	 * @return void
	 */
	protected function _construct()
	{
		parent::_construct();

		$this->setId('patternmanagementGrid');
		$this->setDefaultSort('patternmanagement_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(false);
	}

	/**
	 * @return $this
	 */
	protected function _prepareCollection()
	{
		$collection = $this->_collectionFactory->load();

		$this->setCollection($collection);

		parent::_prepareCollection();

		return $this;
	}

	/**
	 * @return $this
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	protected function _prepareColumns()
	{
		$this->addColumn(
			'patternmanagement_id',
			[
				'header'           => __('ID'),
				'type'             => 'number',
				'index'            => 'patternmanagement_id',
				'header_css_class' => 'col-id',
				'column_css_class' => 'col-id'
			]
		);

		$this->addColumn('brand', [
				'header' => __('Brand'),
				'index'  => 'brand'
			]
		);
		
		$this->addColumn('pattern', [
				'header' => __('Pattern'),
				'index'  => 'pattern'
			]
		);
		
		$this->addColumn('url_key', [
				'header' => __('Url key'),
				'index'  => 'url_key'
			]
		);
		
		$this->addColumn(
			'created_at',
			[
				'header' 	=> __('Created At'),
				'index' 	=> 'created_at',
				'type'      => 'datetime'
			]
		);
		
		$this->addColumn(
			'updated_at',
			[
				'header' 	=> __('Updated At'),
				'index' 	=> 'updated_at',
				'type'      => 'datetime'
			]
		);

		$this->addColumn('status', [
				'header'  => __('Status'),
				'index'   => 'status',
				'type'    => 'options',
				'options' => $this->getStatusOptions(),
			]
		);

		$this->addColumn(
			'edit',
			[
				'header'           => __('Edit'),
				'type'             => 'action',
				'getter'           => 'getId',
				'actions'          => [
					[
						'caption' => __('Edit'),
						'url'     => [
							'base' => '*/*/edit'
						],
						'field'   => 'patternmanagement_id'
					]
				],
				'filter'           => false,
				'sortable'         => false,
				'index'            => 'stores',
				'header_css_class' => 'col-action',
				'column_css_class' => 'col-action'
			]
		);

		$block = $this->getLayout()->getBlock('grid.bottom.links');
		if ($block) {
			$this->setChild('grid.bottom.links', $block);
		}

		return parent::_prepareColumns();
	}

	/**
	 * Filter store condition
	 *
	 * @param \Magento\Framework\Data\Collection $collection
	 * @param \Magento\Framework\DataObject $column
	 * @return void
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
	{
		if (!($value = $column->getFilter()->getValue())) {
			return;
		}

		$this->getCollection()->addStoreFilter($value);
	}

	/**
	 * Get option hash
	 *
	 * @return array
	 */
	protected function getStatusOptions()
	{
		$options = [];
		foreach ($this->_booleanOptions->toOptionArray() as $option) {
			$options[$option['value']] = $option['label'];
		}

		return $options;
	}

	/**
	 * @return $this
	 */
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('patternmanagement_id');
		$this->getMassactionBlock()->setFormFieldName('patternmanagement_id');

		$this->getMassactionBlock()->addItem('delete', [
				'label'   => __('Delete'),
				'url'     => $this->getUrl('brand/*/massDelete'),
				'confirm' => __('Are you sure?')
			]
		);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getGridUrl()
	{
		return $this->getUrl('brand/*/index', ['_current' => true]);
	}

	/**
	 * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
	 * @return string
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', ['patternmanagement_id' => $row->getId()]);
	}
}
