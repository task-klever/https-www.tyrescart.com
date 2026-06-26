<?php
namespace Hdweb\Purchaseorder\Ui\Component\Listing\Column;
use Magento\Ui\Component\Listing\Columns\Column;

class Generatpo extends Column
{
   
    protected $_storeManager;
    protected $backendUrl;
    
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Backend\Model\UrlInterface $backendUrl,
        array $components = [],
        array $data = []
    ){
        $this->_storeManager = $storeManager;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


   public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            
           
            foreach ($dataSource['data']['items'] as $key => $item) {

                //$item['yourcolumn'] is column name
                $params = array('order_id'=>$item['entity_id']);

               $url = $this->backendUrl->getUrl("purchaseorder/create/index", $params);

                $dataSource['data']['items'][$key]['generatpo'] = '<a target="_blank"  href = "' . $url.'">Generate PO</a>'; //H

            }
        }

        return $dataSource;
    }
}