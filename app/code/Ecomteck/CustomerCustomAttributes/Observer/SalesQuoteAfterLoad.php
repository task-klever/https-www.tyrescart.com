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

class SalesQuoteAfterLoad implements ObserverInterface
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Model\Sales\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Model\Sales\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Model\Sales\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * After load observer for quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote instanceof \Magento\Framework\Model\AbstractModel) {
            /** @var $quoteModel \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote */
            $quoteModel = $this->quoteFactory->create();
            $quoteModel->load($quote->getId());
            $quoteModel->attachAttributeData($quote);
        }
        return $this;
    }
}
