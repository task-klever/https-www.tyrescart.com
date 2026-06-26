<?php

namespace TotalPay\Gateway\Model;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\SessionException;
use Magento\Sales\Model\Order;
use TotalPay\Gateway\Api\Data\PaymentInterfaceFactory;
use TotalPay\Gateway\Api\PaymentInformationInterface;

/**
 * Class PaymentInformation
 * @package TotalPay\Gateway\Model
 */
class PaymentInformation implements PaymentInformationInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var \TotalPay\Gateway\Helper\Checkout
     */
    protected \TotalPay\Gateway\Helper\Checkout $checkoutHelper;

    /**
     * @var PaymentInterfaceFactory
     */
    private $dataFactory;

    /**
     * PaymentInformation constructor.
     * @param Session $checkoutSession
     * @param PaymentInterfaceFactory $dataFactory
     */
    public function __construct(
        Session $checkoutSession,
        \TotalPay\Gateway\Helper\Checkout $checkoutHelper,
        PaymentInterfaceFactory $dataFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dataFactory = $dataFactory;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * {@inheritDoc}
     * @param $orderId
     * @return mixed
     * @throws AuthorizationException
     * @throws PaymentException
     * @throws SessionException
     * @throws Exception
     */
    public function getIframeUrl($orderId)
    {
        $order = $this->loadOrder($orderId);
        if ($order->getId() && $payment = $order->getPayment()) {
            /** @var \TotalPay\Gateway\Api\Data\PaymentInterface $data */
            $data = $this->dataFactory->create();
            $data->setOrderId($orderId);
            if ($payment->getMethod() == \TotalPay\Gateway\Model\Method\Checkout::CODE) {
                $redirectUrl = $this->checkoutSession->getTotalPayGatewayCheckoutRedirectUrl();

                if ($redirectUrl) {
                    $data->setRedirectPaymentUrl($redirectUrl);
                } else {
                    throw new PaymentException(__('Cannot get redirect URL'));
                }
            }
            return $data;
        }

        throw new PaymentException(__('Cannot retrieve a payment detail from the request, please contact our support if you have any questions'));
    }

    /**
     * @param int $id
     * @return Order
     * @throws AuthorizationException
     * @throws SessionException
     */
    protected function loadOrder($id)
    {
        $order = $this->checkoutSession->getLastRealOrder();

        if (!$order->getId()) {
            throw new SessionException(__('Your order session is no longer exists due to you may take more than 30 minutes to complete payment transaction with the bank.'));
        }

        if ($id != $order->getId()) {
            throw new AuthorizationException(__('This request is not authorized to access the resource, please contact our support if you have any questions'));
        }

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreCart($cartId)
    {
        $this->getCheckoutHelper()->cancelCurrentOrder('');
        $this->getCheckoutHelper()->restoreQuote();
    }

    /**
     * Get an Instance of the Magento Checkout Helper
     * @return \TotalPay\Gateway\Helper\Checkout
     */
    protected function getCheckoutHelper()
    {
        return $this->checkoutHelper;
    }

}
