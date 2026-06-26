<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderAddressAfterLoad implements ObserverInterface
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Model\Sales\Order\AddressFactory
     */
    protected $orderAddressFactory;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Model\Sales\Order\AddressFactory $orderAddressFactory
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Model\Sales\Order\AddressFactory $orderAddressFactory
    ) {
        $this->orderAddressFactory = $orderAddressFactory;
    }

    /**
     * After load observer for order address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        if ($address instanceof \Magento\Framework\Model\AbstractModel) {
            /** @var $orderAddress \Ecomteck\CustomerCustomAttributes\Model\Sales\Order\Address */
            $orderAddress = $this->orderAddressFactory->create();
            $orderAddress->attachDataToEntities([$address]);
        }
        return $this;
    }
}
