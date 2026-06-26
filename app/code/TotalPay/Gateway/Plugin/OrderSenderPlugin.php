<?php

namespace TotalPay\Gateway\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use TotalPay\Gateway\Model\Method\Checkout;

class OrderSenderPlugin
{
    /**
     * @throws LocalizedException
     */
    public function aroundSend(
        OrderSender $subject,
        \Closure    $proceed,
        Order       $order,
        $forceSyncMode = false
    ) {
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance()->getCode();
        if ($method == Checkout::CODE && $order->getState() == Order::STATE_PAYMENT_REVIEW) {
            if($order->getData('totalpay_paid') == 1){
                return $proceed($order, $forceSyncMode);
            }
            return false;
        }
        return $proceed($order, $forceSyncMode);
    }
}
