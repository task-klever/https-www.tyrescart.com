<?php

namespace Meetanshi\OrderNumber\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * Class Data
 * @package Meetanshi\OrderNumber\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var
     */
    protected $configHelper;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var DateTime
     */
    protected $coreDate;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CollectionFactory
     */
    protected $configDataCollectionFactory;
    /**
     * @var ValueFactory
     */
    protected $configValueFactory;
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param DateTime $coreDate
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $configDataCollectionFactory
     * @param ValueFactory $configValueFactory
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager, DateTime $coreDate, StoreManagerInterface $storeManager, CollectionFactory $configDataCollectionFactory, ValueFactory $configValueFactory, TypeListInterface $cacheTypeList, Pool $cacheFrontendPool)
    {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->coreDate = $coreDate;
        $this->storeManager = $storeManager;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
        $this->configValueFactory = $configValueFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @param $object
     * @param $type
     * @param $sequence
     * @return mixed|string|string[]
     */
    public function prepareIncrementId($object, $type, $sequence)
    {
        $storeId = $object->getStoreId();

        try {
            if (!$sequence) {
                return $sequence;
            }

            if (!$this->checkSupportedType($type)) {
                return $sequence;
            }

            if (!$this->getConfigValue($type, 'enabled', $storeId)) {
                return $sequence;
            }

            if ($type == 'invoice' || $type == 'shipment' || $type == 'creditmemo') {
                if ($this->getConfigValue($type, 'same', $storeId)) {
                    return $sequence;
                }
            }

            $newIncrementId = $this->prepareCustomIncrementId($type, $storeId);
            if (!$newIncrementId || empty($newIncrementId)) {
                return $sequence;
            }

            if ($this->isExist($type, $newIncrementId)) {
                return $sequence;
            }
        } catch (\Exception $e) {
            return $sequence;
        }

        return $newIncrementId;
    }

    /**
     * @param $type
     * @return bool
     */
    public function checkSupportedType($type)
    {
        $supportedTypes = ['order', 'invoice', 'shipment', 'creditmemo'];
        return in_array($type, $supportedTypes);
    }

    /**
     * @param $type
     * @param $field
     * @param $storeId
     * @return mixed
     */
    public function getConfigValue($type, $field, $storeId)
    {
        return $this->scopeConfig->getValue('ordernumber/' . $type . '/' . $field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed|string|string[]
     */
    protected function prepareCustomIncrementId($type, $storeId)
    {
        $format = $this->getConfigValue($type, 'format', $storeId);
        $increment = (int)$this->getConfigValue($type, 'increment', $storeId);
        $padding = (int)$this->getConfigValue($type, 'padding', $storeId);
        $resetCounter = $this->getConfigValue($type, 'reset', $storeId);
        $lastResetDate = $this->getLastConfigValue($type, 'reset_date', $storeId);
        $count = (int)$this->getConfigValue($type, 'count', $storeId);
        $forceResetCounter = $this->getLastConfigValue($type, 'force_reset', $storeId);
        $incrementCounter = $this->getLastConfigValue($type, 'increment_counter', $storeId);
        $currentCounter = (int)$incrementCounter->getValue();

        if ($incrementCounter && $currentCounter > 0) {
            $lastResetDateValue = $lastResetDate->getValue();
            if ($resetCounter !== '' && !empty($lastResetDateValue)) {
                $dateFormat = $resetCounter;
                if ($dateFormat) {
                    $dateHasChanged = false;
                    if ($this->coreDate->date($dateFormat) != $this->coreDate->date($dateFormat, $lastResetDateValue)) {
                        $dateHasChanged = true;
                    }
                    if ($dateHasChanged) {
                        $currentCounter = $count;
                    }
                }
            }

            if ($increment < 1) {
                $increment = 1;
            }
            $newCounter = $currentCounter + $increment;

            if ($forceResetCounter->getValue() === '1') {
                $newCounter = $count;
                $forceResetCounter->setValue('')->save();
            }
        } else {
            $newCounter = $count;
        }

        $currentDate = $this->coreDate->date("Y-m-d");
        $lastResetDate->setValue($currentDate)->save();
        $incrementCounter->setValue($newCounter)->save();

        if ($padding > 0) {
            $newCounter = str_pad($newCounter, $padding, 0, STR_PAD_LEFT);
        }

        $vars = ['store_id' => $storeId, 'store' => $storeId, 'yyyy' => $this->coreDate->date('Y'), 'yy' => $this->coreDate->date('y'), 'mm' => $this->coreDate->date('m'), 'm' => $this->coreDate->date('n'), 'dd' => $this->coreDate->date('d'), 'd' => $this->coreDate->date('j'), 'hh' => $this->coreDate->date('H'), 'h' => $this->coreDate->date('G'), 'ii' => $this->coreDate->date('i'), 'ss' => $this->coreDate->date('s'), 'rand2' => rand(10, 99), 'rand3' => rand(100, 999), 'rand4' => rand(1000, 9999), 'rand5' => rand(10000, 99999), 'rand6' => rand(100000, 999999), 'rand7' => rand(1000000, 9999999), 'rand8' => rand(10000000, 99999999), 'rand9' => rand(100000000, 999999999), 'counter' => $newCounter,];

        $incrementId = $format;
        foreach ($vars as $k => $i) {
            $incrementId = str_replace('{' . $k . '}', $i, $incrementId);
        }

        $types = ['config'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

        return $incrementId;
    }

    /**
     * @param $type
     * @param $field
     * @param $storeId
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLastConfigValue($type, $field, $storeId)
    {
        $scopeId = 0;
        $scope = 'default';
        if ($this->getConfigValue($type, 'per_store', $storeId)) {
            $scopeId = $storeId;
            $scope = 'stores';
        }
        if ($this->getConfigValue($type, 'per_website', $storeId)) {
            $scopeId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $scope = 'websites';
        }

        $collection = $this->configDataCollectionFactory->create();
        $collection = $collection
            ->addFieldToFilter('path', 'ordernumber/' . $type . '/' . $field)
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->setPageSize(1);

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        } else {
            $configData = $this->configValueFactory->create()
                ->setPath('ordernumber/' . $type . '/' . $field)
                ->setScope($scope)
                ->setScopeId($scopeId);
            return $configData;
        }
    }

    /**
     * @param $type
     * @param $incrementId
     * @return bool
     */
    protected function isExist($type, $incrementId)
    {
        if ($type == \Magento\Sales\Model\Order::ENTITY) {
            $entity = '\Magento\Sales\Model\Order';
        } elseif ($type == 'invoice') {
            $entity = '\Magento\Sales\Model\Order\Invoice';
        } elseif ($type == 'shipment') {
            $entity = '\Magento\Sales\Model\Order\Shipment';
        } elseif ($type == 'creditmemo') {
            $entity = '\Magento\Sales\Model\Order\Creditmemo';
        } else {
            return true;
        }

        $ids = $this->objectManager->create($entity)->getCollection()->addAttributeToFilter('increment_id', $incrementId)->getAllIds();
        if (!empty($ids)) {
            return true;
        }

        return false;
    }

    /**
     * @param $type
     * @return |null
     */
    public function loadCollection($type)
    {
        $collection = null;
        if ($this->checkSupportedType($type)) {
            $collection = $this->objectManager->create('Magento\Sales\Model\Order\\' . ucfirst($type))->getCollection();
        }
        return $collection;
    }
}
