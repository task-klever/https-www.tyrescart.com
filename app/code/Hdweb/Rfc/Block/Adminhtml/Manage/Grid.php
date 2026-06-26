<?php
namespace Hdweb\Rfc\Block\Adminhtml\Manage;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Hdweb\Rfc\Model\rfcFactory
     */
    protected $_rfcFactory;

    /**
     * @var \Hdweb\Rfc\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Hdweb\Rfc\Model\rfcFactory $rfcFactory
     * @param \Hdweb\Rfc\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Hdweb\Rfc\Model\RfcFactory $RfcFactory,
        \Hdweb\Rfc\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_rfcFactory = $RfcFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('rfc_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_rfcFactory->create()->getCollection();
        $collection->getSelect()->group('rfc_name');
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
			'rfc_name',
			[
				'header' => __('RFC Name'),
				'index' => 'rfc_name',
                'type' => 'options',
                'options' => \Hdweb\Rfc\Block\Adminhtml\Manage\Grid::getOptionArray4()
                
			]
		);
		
		$this->addColumn(
			'rfc_datetime',
			[
				'header' => __('Executed Date & Time'),
				'index' => 'rfc_name',
				'type'      => 'datetime',
                'renderer'  => 'Hdweb\Rfc\Block\Adminhtml\Manage\Edit\Tab\Getdatetime'
			]
		);

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'rfc_name',
                'renderer'  => 'Hdweb\Rfc\Block\Adminhtml\Manage\Edit\Tab\UrlPath'
            ]
        );	

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    // protected function _prepareMassaction()
    // {

    //     $this->setMassactionIdField('rfc_id');
    //     //$this->getMassactionBlock()->setTemplate('Hdweb_Rfc::rfc/grid/massaction_extended.phtml');
    //     $this->getMassactionBlock()->setFormFieldName('rfc');

    //     $this->getMassactionBlock()->addItem(
    //         'delete',
    //         [
    //             'label' => __('Delete'),
    //             'url' => $this->getUrl('rfc/*/massDelete'),
    //             'confirm' => __('Are you sure?')
    //         ]
    //     );

    //     $statuses = $this->_status->getOptionArray();

    //     $this->getMassactionBlock()->addItem(
    //         'status',
    //         [
    //             'label' => __('Change status'),
    //             'url' => $this->getUrl('rfc/*/massStatus', ['_current' => true]),
    //             'additional' => [
    //                 'visibility' => [
    //                     'name' => 'status',
    //                     'type' => 'select',
    //                     'class' => 'required-entry',
    //                     'label' => __('Status'),
    //                     'values' => $statuses
    //                 ]
    //             ]
    //         ]
    //     );


    //     return $this;
    // }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('rfc/*/index', ['_current' => true]);
    }

    /**
     * @param \Hdweb\Rfc\Model\rfc|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return '#';
    }

	
		static public function getOptionArray3()
		{
            $data_array=array(); 
			$data_array['Success']='Success';
			$data_array['Failed']='Failed';
            $data_array['Running']='Running';
            return($data_array);
		}
		static public function getValueArray3()
		{
            $data_array=array();
			foreach(\Hdweb\Rfc\Block\Adminhtml\Rfc\Grid::getOptionArray3() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
        static public function getOptionArray4()
        {
            $data_array=array(); 
            $data_array['Product Stock Update'] ='Product Stock Update';
            // $data_array['Product Stock Update UAE'] ='Product Stock Update UAE';
            return($data_array);
        }
        static public function getValueArray4()
        {
            $data_array=array();
            foreach(\Hdweb\Rfc\Block\Adminhtml\Rfc\Grid::getOptionArray4() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);        
            }
            return($data_array);

        }
		

}