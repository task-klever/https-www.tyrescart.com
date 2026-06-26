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

class SalesOrderAfterSave implements ObserverInterface
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Model\Sales\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Model\Sales\OrderFactory $orderFactory
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Model\Sales\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
    }

    /**
     * After save observer for order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            /** @var $orderModel \Ecomteck\CustomerCustomAttributes\Model\Sales\Order */
            $orderModel = $this->orderFactory->create();
            $orderModel->saveAttributeData($order);
        }
        return $this;
    }
}
