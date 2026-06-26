<?php
namespace Hdweb\Purchaseorder\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Poqty extends Column
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
    ) {
        $this->_storeManager = $storeManager;
        $this->backendUrl    = $backendUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item)
            {
                //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
               // $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($item['entity_id']);
                $item[$this->getData('name')] = 10;
            }
        }
        return $dataSource;
    }
}
