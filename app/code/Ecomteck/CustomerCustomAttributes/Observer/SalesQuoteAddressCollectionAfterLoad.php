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

class SalesQuoteAddressCollectionAfterLoad implements ObserverInterface
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory $quoteAddressFactory
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory $quoteAddressFactory
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * After load observer for collection of quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getQuoteAddressCollection();
        if ($collection instanceof \Magento\Framework\Data\Collection\AbstractDb) {
            /** @var $quoteAddress \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote\Address */
            $quoteAddress = $this->quoteAddressFactory->create();
            $quoteAddress->attachDataToEntities($collection->getItems());
        }
        return $this;
    }
}
