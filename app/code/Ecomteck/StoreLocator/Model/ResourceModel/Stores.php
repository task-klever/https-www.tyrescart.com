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
namespace Ecomteck\StoreLocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Ecomteck\StoreLocator\Model\Stores as StoresModel;
use Magento\Framework\Event\ManagerInterface;

class Stores extends AbstractDb
{
    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    public $store = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    public $dateTime;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    public $eventManager;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager
    ) {
        $this->date             = $date;
        $this->storeManager     = $storeManager;
        $this->dateTime         = $dateTime;
        $this->eventManager     = $eventManager;

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ecomteck_storelocator_stores', 'stores_id');
    }

    /**
     * Process stores data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _beforeDelete(AbstractModel $object)
    {
        $condition = ['stores_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('ecomteck_storelocator_stores'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * before save callback
     *
     * @param AbstractModel|\Ecomteck\StoreLocator\Model\Stores $object
     * @return $this
     */
    public function _beforeSave(AbstractModel $object)
    {

        $object->setUpdatedAt($this->date->gmtDate());
        
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->gmtDate());
        }

        return parent::_beforeSave($object);
    }


    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }


    /**
     * @param AbstractModel $object
     * @param $attribute
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(AbstractModel $object, $attribute)
    {
        if (is_string($attribute)) {
            $attributes = [$attribute];
        } else {
            $attributes = $attribute;
        }
        if (is_array($attributes) && !empty($attributes)) {
            $this->getConnection()->beginTransaction();
            $data = array_intersect_key($object->getData(), array_flip($attributes));
            try {
                $this->beforeSaveAttribute($object, $attributes);
                if ($object->getId() && !empty($data)) {
                    $this->getConnection()->update(
                        $object->getResource()->getMainTable(),
                        $data,
                        [$object->getResource()->getIdFieldName() . '= ?' => (int)$object->getId()]
                    );
                    $object->addData($data);
                }
                $this->afterSaveAttribute($object, $attributes);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @param $attribute
     * @return $this
     */
    public function beforeSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_before',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }

    /**
     * After save object attribute
     *
     * @param AbstractModel $object
     * @param string $attribute
     * @return \Magento\Sales\Model\ResourceModel\Attribute
     */
    public function afterSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_after',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }

    /**
     * Additional (featured) products for current slider
     *
     * @param \Ecomteck\ProductSlider\Model\ProductSlider $store
     * @return array
     */
    public function getProductsPosition($store)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('ecomteck_storelocator_products'),
            ['product_id', 'position']
        )->where(
            'stores_id = :stores_id'
        );

        $bind = ['stores_id' => (int)$store->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Update (save new or delete old) additional slider products
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _updateStoreProducts($store)
    {
        $id = $store->getId();

        /**
         * new slider-product relationships
         */
        $products = $store->getPostedProducts();

        /**
         * Example re-save slider
         */
        if ($products === null) {
            return $this;
        }

        /**
         * old slider-product relationships
         */
        $oldProducts = $store->getProductsPosition();

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $connection = $this->getConnection();

        /**
         * Delete products from slider
         */
        if (!empty($delete)) {
            $condition = ['product_id IN(?)' => array_keys($delete), 'stores_id=?' => $id];
            $connection->delete($this->getTable('ecomteck_storelocator_products'), $condition);
        }

        /**
         * Add products to slider
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'stores_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position,
                ];
            }
            $connection->insertMultiple($this->getTable('ecomteck_storelocator_products'), $data);
        }

        /**
         * Update product positions in category
         */
        if (!empty($update)) {
            foreach ($update as $productId => $position) {
                $where = ['stores_id = ?' => (int)$id, 'product_id = ?' => (int)$productId];
                $bind = ['position' => (int)$position];
                $connection->update($this->getTable('ecomteck_storelocator_products'), $bind, $where);
            }
        }

        return $this;
    }

    /**
     * Perform actions after object (store) save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_updateStoreProducts($object);
        return parent::_afterSave($object);
    }
}
