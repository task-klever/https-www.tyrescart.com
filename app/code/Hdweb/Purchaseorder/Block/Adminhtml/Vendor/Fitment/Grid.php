<?php

namespace Hdweb\Purchaseorder\Block\Adminhtml\Vendor\Fitment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $_povendorfitment;
    protected $_resource;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Framework\App\ResourceConnection $Resource, \Magento\Framework\Registry $coreRegistry, \Hdweb\Purchaseorder\Model\Povendorfitment $povendorfitment, array $data = []
    ) {
        $this->_povendorfitment = $povendorfitment;
        $this->_coreRegistry = $coreRegistry;
        $this->_resource = $Resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('basicsize_tab_grid');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection() {
        $collection = $this->_povendorfitment->getCollection()->addFieldToSelect("*")
                ->addFilter("vendor_id", $this->getRequest()->getParam("id"));

        $collection->setOrder('sku', 'ASC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns() {
        $this->addColumn(
                'sku', [
            'header' => __('SKU'),
            'sortable' => false,
            'filter' => false,
            'index' => 'sku',
                ]
        );
        $this->addColumn(
                'vendor_price', [
            'header' => __('Vendor Price'),
            'index' => 'vendor_price',
            'filter' => false,
            'sortable' => false,
                ]
        );
        
        $this->addColumn(
                'tiresize_delete', [
            'header_css_class' => 'a-center',
            'header' => __('Delete'),
            'type' => 'checkbox',
            'field_name' => 'tiresize_delete[]',
            'align' => 'center',
            'index' => 'id',
            'filter' => false,
            'sortable' => false,
                ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl() {
        //return $this->getUrl('customshoeadmin/toematerial/action', ['_current' => true]);
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getServiceOptionArray() {

        $data_array = array();
        $data_array[0] = '';
        $data_array[1] = 'N/A';
        $data_array[2] = 'N/C';
        return($data_array);
    }

}
