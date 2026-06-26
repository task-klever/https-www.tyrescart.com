<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Model\ResourceModel\Stores;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ecomteck\StoreLocator\Model\Stores;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores as StoresResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    public $_idFieldName = 'stores_id';
    /**
     * Event prefix
     *
     * @var string
     */
    public $_eventPrefix = 'ecomteck_storelocator_stores_collection';

    /**
     * Event object
     *
     * @var string
     */
    public $_eventObject = 'stores_collection';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * Store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var array
     */
    public $_joinedFields = [];

    /**
     * constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param null $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        $connection = null,
        ?AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Stores::class, StoresResourceModel::class);
        $this->_map['fields']['stores_id'] = 'main_table.stores_id';
    }

    /**
     * after collection load
     *
     * @return $this
     */
    public function _afterLoad()
    {
        return parent::_afterLoad();
    }

    /**
     * after collection load
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Store) {
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $store = [$store];
            }

            if ($withAdmin) {
                $store[] = Store::DEFAULT_STORE_ID;
            }

            $this->addFilter('store_id', ['in' => $store], 'public');
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    public function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }

    /**
     * @param $tableName
     * @param $linkField
     */
    public function performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['ecomteck_storelocator_stores' => $this->getTable($tableName)])
                ->where('ecomteck_storelocator_stores.' . $linkField . ' IN (?)', $linkedIds);
            // @codingStandardsIgnoreStart
            $result = $connection->fetchAll($select);
            // @codingStandardsIgnoreEnd
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }

    public function addActiveFilter()
    {
        $this->addFieldToFilter('status', 1);
        return $this;
    }

    public function addProductFilter($productId) 
    {
        if($this->isAvailableAllStores()){
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['store_products'=>$this->getTable('ecomteck_storelocator_products')],
            'main_table.stores_id = store_products.stores_id',
            [])->where('store_products.product_id=?',$productId);
        return $this;
    }

    public function addProductsFilter($productIds)
    {
        if($this->isAvailableAllStores()){
            return $this;
        }
        if(empty($productIds)){
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['store_products'=>$this->getTable('ecomteck_storelocator_products')],
            'main_table.stores_id = store_products.stores_id',
            [])->where('store_products.product_id IN (?)',$productIds);
        return $this;
    }

    protected function isAvailableAllStores()
    {
        return $this->scopeConfig->getValue('ecomteck_storelocator/products/is_available_stores', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function toInstallerOptionArray()
    {
        $collection = $this->_afterLoad();
        $items = [];
        $item = ['value' => '', 'label' =>__('-- Please Select Installer --')];
        $items[ ] = $item;
        foreach ($collection as $block){
            $item = ['value' => $block->getStoresId(), 'label' =>$block->getName()];
            $items[ ] = $item;
        }
        
        return $items;
    }

}
