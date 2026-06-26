<?php

namespace Meetanshi\OrderNumber\Observer;

use Magento\Sales\Model\EntityInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\OrderNumber\Helper\Data;
use Magento\SalesSequence\Model\Manager;

/**
 * Class SalesAbstractObserver
 * @package Meetanshi\OrderNumber\Observer
 */
class SalesAbstractObserver implements ObserverInterface
{
    /**
     * @var array
     */
    protected $orderType = ['invoice', 'shipment', 'creditmemo'];

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * SalesAbstractObserver constructor.
     * @param Data $helper
     * @param Manager $manager
     */
    public function __construct(Data $helper, Manager $manager)
    {
        $this->helper = $helper;
        $this->manager = $manager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $type = '';
        $customData = null;

        foreach ($this->orderType as $data) {
            if (is_object($observer->getData($data))) {
                $type = $data;
            }
        }

        if (!$type) {
            return;
        }

        if (in_array($type, $this->orderType)) {
            $object = $observer->getData($type);
        }

        if (!$object->getId()) {
            $order = $object->getOrder();
            $storeId = $order->getStoreId();

            if (!$this->helper->getConfigValue($type, 'enabled', $storeId)) {
                return $this;
            }

            if (!$this->helper->getConfigValue($type, 'same', $storeId)) {
                if ($object instanceof EntityInterface && $object->getIncrementId() == null) {
                    $originalSequence = $this->manager->getSequence(
                        $object->getEntityType(),
                        $object->getStore()->getGroup()->getDefaultStoreId()
                    )->getNextValue();
                    $incrementId = $this->helper->prepareIncrementId($object, $type, $originalSequence);
                    $object->setIncrementId($incrementId);
                }
                return $this;
            }

            $orderIncrementId = $order->getIncrementId();
            $prefix = $this->helper->getConfigValue($type, 'prefix', $storeId);
            $replaceWith = $this->helper->getConfigValue($type, 'replace_with', $storeId);
            if (!empty($replaceWith)) {
                $orderIncrementId = str_replace($replaceWith, "", $orderIncrementId);
            }
            if (empty($orderIncrementId)) {
                return $this;
            }

            $collection = $this->helper->loadCollection($type);

            $maxIterations = 99;
            $newIncrementId = false;
            $subIncrementIdCounter = 0;
            while ($newIncrementId === false) {
                if ($subIncrementIdCounter > $maxIterations) {
                    break;
                }
                if ($subIncrementIdCounter > 0) {
                    $newIncrementId = $prefix . $orderIncrementId . '-' . $subIncrementIdCounter;
                } else {
                    $newIncrementId = $prefix . $orderIncrementId;
                }
                $collection->clear();
                $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                $collection->getSelect()->where('increment_id = ?', $newIncrementId);
                if ($collection->count() > 0) {
                    $newIncrementId = false;
                    $subIncrementIdCounter++;
                } else {
                    $object->setIncrementId($newIncrementId);
                    break;
                }
            }
        }
        return $this;
    }
}
