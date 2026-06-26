<?php

declare(strict_types=1);

namespace Tamara\Checkout\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Tamara\Checkout\Api\CheckoutInformationRepositoryInterface;

class PlaceOrderService extends AbstractPlaceOrderService
{
    private CheckoutInformationRepositoryInterface $checkoutInformationRepository;

    public function __construct(
        CartManagementInterface $cartManagement,
        CheckoutInformationRepositoryInterface $checkoutInformationRepository
    ) {
        parent::__construct($cartManagement);
        $this->checkoutInformationRepository = $checkoutInformationRepository;
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        if ($orderId) {
            try {
                $checkoutInfo = $this->checkoutInformationRepository->getTamaraCheckoutInformation($orderId);
                if ($checkoutInfo && $checkoutInfo->getRedirectUrl()) {
                    return $checkoutInfo->getRedirectUrl();
                }
            } catch (\Exception $e) {
                // Fall through to default
            }
        }

        return parent::getRedirectUrl($quote, $orderId);
    }
}
