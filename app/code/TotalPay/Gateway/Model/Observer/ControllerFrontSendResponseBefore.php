<?php


namespace TotalPay\Gateway\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer Class (called just before the Response on the Front Site is sent)
 * Used to overwrite the Exception on the Checkout Page
 *
 * Class ControllerFrontSendResponseBefore
 * @package TotalPay\Gateway\Model\Observer
 */
class ControllerFrontSendResponseBefore implements ObserverInterface
{
    /**
     * @var \TotalPay\Gateway\Helper\Data
     */
    protected $_moduleHelper;

    /**
     * @var \Magento\Framework\Webapi\ErrorProcessor
     */
    protected $_errorProcessor;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * SalesOrderPaymentPlaceEnd constructor.
     * @param \TotalPay\Gateway\Helper\Data $moduleHelper
     * @param \Magento\Framework\Webapi\ErrorProcessor $errorProcessor
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \TotalPay\Gateway\Helper\Data $moduleHelper,
        \Magento\Framework\Webapi\ErrorProcessor $errorProcessor,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_moduleHelper = $moduleHelper;
        $this->_errorProcessor = $errorProcessor;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $response = $observer->getEvent()->getData('response');

            if ($response && $this->getShouldOverrideCheckoutException($response)) {
                /** @var \Magento\Framework\Webapi\Rest\Response $response */

                $maskedException = $this->getModuleHelper()->createWebApiException(
                    $this->getTotalPayGatewayLastCheckoutError(),
                    \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
                );

                $response->setException($maskedException);
                $this->clearTotalPayGatewayLastCheckoutError();
            }
        } catch (\Exception $e) {
            /**
             * Just hide any exception (if occurs) when trying to override exception message
             */
        }
    }

    /**
     * @param $response
     * @return bool
     */
    protected function getShouldOverrideCheckoutException($response)
    {
        return
            ($this->getTotalPayGatewayLastCheckoutError()) &&
            ($response instanceof \Magento\Framework\Webapi\Rest\Response) &&
            (method_exists($response, 'isException')) &&
            ($response->isException());
    }

    /**
     * Retrieves the last error message from the session, which has occurred on the checkout page
     *
     * @return mixed
     */
    protected function getTotalPayGatewayLastCheckoutError()
    {
        return $this->getCheckoutSession()->getTotalPayGatewayLastCheckoutError();
    }

    /**
     * Clears the last error from the session, which occurs on the checkout page
     *
     * @return void
     */
    protected function clearTotalPayGatewayLastCheckoutError()
    {
        $this->getCheckoutSession()->setTotalPayGatewayLastCheckoutError(null);
    }

    /**
     * @return \TotalPay\Gateway\Helper\Data
     */
    protected function getModuleHelper()
    {
        return $this->_moduleHelper;
    }

    /**
     * @return \Magento\Framework\Webapi\ErrorProcessor
     */
    protected function getErrorProcessor()
    {
        return $this->_errorProcessor;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}
