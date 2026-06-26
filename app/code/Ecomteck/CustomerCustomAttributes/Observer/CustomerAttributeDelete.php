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

class CustomerAttributeDelete implements ObserverInterface
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Model\Sales\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Ecomteck\CustomerCustomAttributes\Model\Sales\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Model\Sales\OrderFactory $orderFactory
     * @param \Ecomteck\CustomerCustomAttributes\Model\Sales\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Model\Sales\OrderFactory $orderFactory,
        \Ecomteck\CustomerCustomAttributes\Model\Sales\QuoteFactory $quoteFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * After delete observer for customer attribute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof \Magento\Customer\Model\Attribute && !$attribute->isObjectNew()) {
            /** @var $quoteModel \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote */
            $quoteModel = $this->quoteFactory->create();
            $quoteModel->deleteAttribute($attribute);
            /** @var $orderModel \Ecomteck\CustomerCustomAttributes\Model\Sales\Order */
            $orderModel = $this->orderFactory->create();
            $orderModel->deleteAttribute($attribute);
        }
        return $this;
    }
}
