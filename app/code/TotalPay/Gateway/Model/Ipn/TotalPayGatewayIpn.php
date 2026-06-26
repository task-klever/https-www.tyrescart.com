<?php


namespace TotalPay\Gateway\Model\Ipn;

/**
 * Checkout Method IPN Handler Class
 * Class CheckoutIpn
 * @package TotalPay\Gateway\Model\Ipn
 */
class TotalPayGatewayIpn extends \TotalPay\Gateway\Model\Ipn\AbstractIpn
{
    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return \TotalPay\Gateway\Model\Method\Checkout::CODE;
    }

    /**
     * Update Pending Transactions and Order Status
     * @param \stdClass $responseObject
     * @throws \Exception
     */
    protected function processNotification($responseObject)
    {
        $payment = $this->getPayment();
        $helper = $this->getModuleHelper();

        $this->getModuleHelper()->updateTransactionAdditionalInfo(
            $responseObject['id'],
            $responseObject,
            true
        );

        $payment_transaction = $responseObject;
        $payment
            ->setLastTransId(
                $payment_transaction['id'],
            )
            ->setTransactionId(
                $payment_transaction['id'],
            )
            ->setParentTransactionId(
                isset(
                    $responseObject['order_number']
                ) ?
                    $responseObject['order_number']
                    : null
            )
            ->setIsTransactionPending(
                $this->getShouldSetCurrentTranPending(
                    $payment_transaction
                )
            )
            ->setShouldCloseParentTransaction(
                true
            )
            ->setIsTransactionClosed(false)
            ->setPreparedMessage(
                $this->createIpnComment(
                    'successful paid'
                )
            )
            ->resetTransactionAdditionalInfo();


        $this->getModuleHelper()->setPaymentTransactionAdditionalInfo(
            $payment,
            $payment_transaction
        );
        $payment->registerCaptureNotification($payment_transaction['order_amount']);
        $payment->save();
        $this->getModuleHelper()->setOrderStatusByState(
            $this->getOrder(),
            \Magento\Sales\Model\Order::STATE_PROCESSING
        );
        $this->getOrder()->save();
    }
}
