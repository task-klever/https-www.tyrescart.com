<?php


namespace TotalPay\Gateway\Controller\Checkout;

/**
 * Front Controller for Checkout Method
 * it does a redirect to checkout
 * Class Index
 * @package TotalPay\Gateway\Controller\Checkout
 */
class Index extends \TotalPay\Gateway\Controller\AbstractCheckoutAction
{
    /**
     * Redirect to checkout
     *
     * @return void
     */
    public function execute()
    {

        $order = $this->getOrder();

        if (isset($order)) {
            $redirectUrl = $this->getCheckoutSession()->getTotalPayGatewayCheckoutRedirectUrl();

            if (isset($redirectUrl)) {
                $this->getResponse()->setRedirect($redirectUrl);
            } else {
                $this->redirectToCheckoutFragmentPayment();
            }
        }
    }
}
