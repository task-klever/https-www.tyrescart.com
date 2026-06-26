<?php

declare(strict_types=1);

namespace Tabby\Checkout\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

class PlaceOrderService extends AbstractPlaceOrderService
{
    public function __construct(
        CartManagementInterface $cartManagement
    ) {
        parent::__construct($cartManagement);
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        return 'tabby/redirect';
    }
}
