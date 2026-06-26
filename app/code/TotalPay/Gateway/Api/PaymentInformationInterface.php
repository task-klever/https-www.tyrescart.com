<?php

namespace TotalPay\Gateway\Api;

use TotalPay\Gateway\Api\Data\PaymentInterface;

/**
 * Interface PaymentInformationInterface
 * @package TotalPay\Gateway\Api
 */
interface PaymentInformationInterface
{
    /**
     * @param int $orderId
     * @return PaymentInterface
     */
    public function getIframeUrl($orderId);

    /**
     * Restore cart.
     *
     * @param string $cartId
     *
     * @return mixed
     */
    public function restoreCart($cartId);
}
