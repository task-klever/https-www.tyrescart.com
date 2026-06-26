<?php

namespace Ecomteck\StorePickup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class ResetInstallerOnCartLoad implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();

        // Reset installer selection
        $quote->setPickupDate(null);
        $quote->setPickupTime(null);
        $quote->setPickupStore(null);
        $quote->setPickupLocation(null);
        $quote->setFitmentType(null);

        // Reset shipping amount and description
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getId()) {
            $shippingAddress->setShippingAmount(0);
            $shippingAddress->setBaseShippingAmount(0);
            $shippingAddress->setShippingDescription('');
            $shippingAddress->save();
        }

        $quote->save();
    }
}
