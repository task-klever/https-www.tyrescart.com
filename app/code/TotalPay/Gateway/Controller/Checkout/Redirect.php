<?php


namespace TotalPay\Gateway\Controller\Checkout;

/**
 * Return Action Controller (used to handle Redirects from the Payment Gateway)
 *
 * Class Redirect
 * @package TotalPay\Gateway\Controller\Checkout
 */
class Redirect extends \TotalPay\Gateway\Controller\AbstractCheckoutRedirectAction
{
    /**
     * Handle the result from the Payment Gateway
     *
     * @return void
     */
    public function execute()
    {
        switch ($this->getReturnAction()) {
            case 'success':
                $this->executeSuccessAction();
                break;

            case 'cancel':
                $this->getMessageManager()->addWarning(
                    __("You have successfully canceled your order")
                );
                $this->executeCancelAction();
                break;

            case 'failure':
                $this->getMessageManager()->addError(
                    __("Please, check your input and try again!")
                );
                $this->executeCancelAction();
                break;

            default:
                $this->getResponse()->setHttpResponseCode(
                    \Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED
                );
        }
    }
}
