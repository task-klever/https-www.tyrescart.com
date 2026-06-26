<?php

declare(strict_types=1);

namespace TotalPay\Gateway\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

class PlaceOrderService extends AbstractPlaceOrderService
{
    private CheckoutSession $checkoutSession;

    public function __construct(
        CartManagementInterface $cartManagement,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($cartManagement);
        $this->checkoutSession = $checkoutSession;
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $redirectUrl = $this->checkoutSession->getTotalPayGatewayCheckoutRedirectUrl();

        if ($redirectUrl) {
            return $redirectUrl;
        }

        return 'totalpay/checkout/index';
    }
}
