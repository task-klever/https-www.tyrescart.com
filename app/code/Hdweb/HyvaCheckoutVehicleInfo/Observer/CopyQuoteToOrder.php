<?php

namespace Hdweb\HyvaCheckoutVehicleInfo\Observer;

use Magento\Framework\Event\ObserverInterface;

class CopyQuoteToOrder implements ObserverInterface
{
    protected $quoteRepository;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
    ) {
        $this->quoteRepository   = $quoteRepository;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        
        $order->setPlate($quote->getPlate());
        $order->setMake($quote->getMake());
        $order->setModel($quote->getModel());
        $order->setYear($quote->getYear());

        return $this;
    }
}
