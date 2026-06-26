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

class SalesQuoteAddressAfterSave implements ObserverInterface
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
     * After save observer for quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteAddress = $observer->getEvent()->getQuoteAddress();
        if ($quoteAddress instanceof \Magento\Framework\Model\AbstractModel) {
            /** @var $quoteAddressModel \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote\Address */
            $quoteAddressModel = $this->quoteAddressFactory->create();
            $quoteAddressModel->saveAttributeData($quoteAddress);
        }
        return $this;
    }
}
