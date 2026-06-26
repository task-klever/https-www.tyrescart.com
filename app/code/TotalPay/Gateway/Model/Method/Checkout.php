<?php

namespace TotalPay\Gateway\Model\Method;

/**
 * Checkout Payment Method Model Class
 * Class Checkout
 * @package TotalPay\Gateway\Model\Method
 */
class Checkout extends \Magento\Payment\Model\Method\AbstractMethod
{
    use \TotalPay\Gateway\Model\Traits\OnlinePaymentMethod;
    use \TotalPay\Gateway\Model\Traits\Logger;

    const CODE = 'totalpay_checkout';
    /**
     * Checkout Method Code
     */
    protected $_code = self::CODE;

    protected $_canOrder = true;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canCancelInvoice = true;
    protected $_canVoid = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canAuthorize = true;
    protected $_isInitializeNeeded = false;

    /**
     * Get Instance of the Magento Code Logger
     * @return \Zend\Log\Logger
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Checkout constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\App\Action\Context $actionContext
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \TotalPay\Gateway\Helper\Data $moduleHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Action\Context $actionContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \TotalPay\Gateway\Helper\Data $moduleHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_actionContext = $actionContext;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_moduleHelper = $moduleHelper;


        $this->_logger = $this->_initLogger();

        $this->_configHelper =
            $this->getModuleHelper()->getMethodConfig(
                $this->getCode()
            );
    }

    /**
     * Get Default Payment Action On Payment Complete Action
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
    }

    /**
     * Get Available Checkout Transaction Types
     * @return array
     */
    public function getCheckoutTransactionTypes()
    {
        $selected_types = $this->getConfigHelper()->getTransactionTypes();

        return $selected_types;
    }

    /**
     * Get Available Checkout Payment Method Types
     * @return array
     */
    public function getCheckoutPaymentMethodTypes()
    {
        $selected_types = $this->getConfigHelper()->getPaymentMethodTypes();

        return $selected_types;
    }

    /**
     * Create a Web-Payment Form Instance
     * @param array $data
     * @return \stdClass
     * @throws \Magento\Framework\Webapi\Exception
     */
    protected function checkout($data)
    {
        $state = null;
        /**if (in_array(strval($data['order']['billing']->getCountryId()), array('US', 'CA'))) {
         * $billingAddress = $data['order']['billing']->getData();
         * $region = $this->getRegionFactory()->create()->load($billingAddress['region_id']);
         * $state = $region->getCode();
         * }*/
        //build json
        $customer = [
            'name' => strval($data['order']['billing']->getFirstname()) . ' ' . strval($data['order']['billing']->getLastname()),
            'email' => strval($data['order']['customer']['email']),
        ];
        $billing_address = [
            'country' => strval($data['order']['billing']->getCountryId()),
            'state' => isset($state) ? $state : 'None',
            'city' => strval($data['order']['billing']->getCity()),
            'address' => strval($data['order']['billing']->getStreetLine(1)),
            'zip' => $data['order']['billing']->getPostcode(),
            'phone' => strval($data['order']['billing']->getTelephone()),
        ];
        $order_json = [
            'number' => $data['order']['increment_id'],
            'description' => $data['order']['description'],
            'amount' => number_format($data['order']['amount'], 2, '.', ''), //may troubles
            'currency' => $data['order']['currency'],
        ];

        $methods = $this->getConfigHelper()->getPaymentMethodTypes(); //may error
        // $methods = array('card');

        $str_to_hash = $data['order']['increment_id'] . number_format($data['order']['amount'], 2, '.',
                '') . $data['order']['currency'] . $data['order']['description'] . $this->getConfigHelper()->getShopKey();
        $hash = sha1(md5(strtoupper($str_to_hash)));

        $formId = $this->getConfigHelper()->getFormId();
        $isUseIframe = $this->getConfigHelper()->isUseIframe();
        if ($isUseIframe) {
            $postData = [
                'merchant_key' => $this->getConfigHelper()->getShopId(),
                'operation' => 'purchase', //m subs purchase
                //            'methods'      => $methods,
                'order' => $order_json,
                'customer' => $customer,
                'billing_address' => $billing_address,
                'success_url' => $this->getModuleHelper()->getReturnUrl($this->getCode(), 'success'), //dodelat
                'cancel_url' => $this->getModuleHelper()->getReturnUrl($this->getCode(), 'cancel'), //dodelat
                'hash' => $hash,
                'url_target' => '_parent'
            ];
        } else {
            $postData = [
                'merchant_key' => $this->getConfigHelper()->getShopId(),
                'operation' => 'purchase', //m subs purchase
                //            'methods'      => $methods,
                'order' => $order_json,
                'customer' => $customer,
                'billing_address' => $billing_address,
                'success_url' => $this->getModuleHelper()->getReturnUrl($this->getCode(), 'success'), //dodelat
                'cancel_url' => $this->getModuleHelper()->getReturnUrl($this->getCode(), 'cancel'), //dodelat
                'hash' => $hash
            ];
        }

        if ($formId) {
            $postData['form_id'] = $formId;
        }
        //build json


        $this->_logger->debug('token_requestJSON: ' . json_encode($postData));

        $response = $this->doRequest($postData, $this->getConfigHelper()->getDomainCheckout());

        $this->_logger->debug('token_responseJSON:' . $response);


        return $response;
    }

    /**
     * Order Payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();

        $orderId = ltrim(
            $order->getIncrementId(),
            '0'
        );
        $ord_id = $this->getModuleHelper()->genTransactionId($order->getIncrementId());

        $data = [
            'order' => [
                'increment_id' => $ord_id,
                'currency' => $order->getBaseCurrencyCode(),
                'amount' => $amount,
                //'usage' => $this->getModuleHelper()->buildOrderUsage(),
                'description' => __('Order # %1 payment', $orderId),
                'customer' => [
                    'email' => $this->getCheckoutSession()->getQuote()->getCustomerEmail(),
                ],
                'billing' =>
                    $order->getBillingAddress(),
                'shipping' =>
                    $order->getShippingAddress()
            ],
        ];

        try {

            $responseObject = $this->checkout($data);
            $responseArr = json_decode((string)$responseObject, true);

            if (!isset($responseArr['redirect_url'])) {
                $this->_logger->debug(print_r($responseArr, true));
            }

            $payment->setTransactionId($ord_id);
            $payment->setIsTransactionPending(true);
            $payment->setIsTransactionClosed(false);

            $this->getModuleHelper()->setPaymentTransactionAdditionalInfo(
                $payment,
                $responseArr
            );
            $payment->setTransactionAdditionalInfo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $responseArr);


            $this->getCheckoutSession()->setTotalPayGatewayCheckoutRedirectUrl($responseArr['redirect_url']);


            //$this->_writeDebugData();
            return $this;
        } catch (\Exception $e) {
            $this->_logger->debug('exceptionORDER: ' . $e->getMessage());

            $this->getCheckoutSession()->setTotalPayGatewayLastCheckoutError(
                $e->getMessage()
            );
            //$this->_writeDebugData();
            //$this->getModuleHelper()->maskException($e);
        }

    }

    /**
     * Payment Capturing
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // /** @var \Magento\Sales\Model\Order $order */
        // $order = $payment->getOrder();

        // $this->_addDebugData('capture_process', 'Capture transaction for order #' . $order->getIncrementId());

        // $authTransaction = $this->getModuleHelper()->lookUpAuthorizationTransaction(
        //     $payment
        // );

        // if (!isset($authTransaction)) {
        //     $errorMessage = __('Capture transaction for order # %1 cannot be finished (No Authorize Transaction exists)',
        //         $order->getIncrementId()
        //     );

        //     $this->_addDebugData('capture_error', $errorMessage);
        //     $this->_writeDebugData();

        //     $this->getModuleHelper()->throwWebApiException(
        //         $errorMessage
        //     );
        // }

        // try {
        //     $this->doCapture($payment, $amount, $authTransaction);
        // } catch (\Exception $e) {
        //     $this->_addDebugData('exception', $e->getMessage());
        //     $this->_writeDebugData();
        //     $this->getModuleHelper()->maskException($e);
        // }
        // $this->_writeDebugData();

        return $this;
    }

    /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        error_log('refund triggerrrrrrrrrrrrrrr');

        $this->_addDebugData('refund_process', 'Refund transaction for order #' . $order->getIncrementId());

        $captureTransaction = $this->getModuleHelper()->lookUpCaptureTransaction(
            $payment
        );

        error_log('refund triggerrrrrrrrrrrrrrr1');

        if (!isset($captureTransaction)) {
            $errorMessage = __('Refund transaction for order # %1 cannot be finished (No Capture Transaction exists)',
                $order->getIncrementId()
            );

            $this->_addDebugData('refund_error', $errorMessage);
            $this->_writeDebugData();

            $this->getMessageManager()->addError($errorMessage);

            $this->getModuleHelper()->throwWebApiException(
                $errorMessage
            );
        }

        error_log('refund triggerrrrrrrrrrrrrrr2');

        try {
            $this->doRefund($payment, $amount, $captureTransaction);
        } catch (\Exception $e) {
            $this->_addDebugData('exception', $e->getMessage());
            $this->_writeDebugData();

            $this->getMessageManager()->addError(
                $e->getMessage()
            );

            $this->getModuleHelper()->maskException($e);
        }
        $this->_writeDebugData();

        return $this;
    }

    /**
     * Payment Cancel
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->void($payment);
        return $this;
    }

    /**
     * Void Payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        // /** @var \Magento\Sales\Model\Order $order */

        // $order = $payment->getOrder();

        // $this->_addDebugData('void_process', 'Void transaction for order #' . $order->getIncrementId());

        // $referenceTransaction = $this->getModuleHelper()->lookUpVoidReferenceTransaction(
        //     $payment
        // );

        // if ($referenceTransaction->getTxnType() == \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH) {
        //     $authTransaction = $referenceTransaction;
        // } else {
        //     $authTransaction = $this->getModuleHelper()->lookUpAuthorizationTransaction(
        //         $payment
        //     );
        // }

        // if (!isset($authTransaction) || !isset($referenceTransaction)) {
        //     $errorMessage = __('Void transaction for order # %1 cannot be finished (No Authorize / Capture Transaction exists)',
        //                     $order->getIncrementId()
        //     );
        //     $this->_addDebugData('void_error', $errorMessage);
        //     $this->_writeDebugData();

        //     $this->getModuleHelper()->throwWebApiException($errorMessage);
        // }

        // try {
        //     $this->doVoid($payment, $authTransaction, $referenceTransaction);
        // } catch (\Exception $e) {
        //     $this->_addDebugData('exception', $e->getMessage());
        //     $this->_writeDebugData();
        //     $this->getModuleHelper()->maskException($e);
        // }
        // $this->_writeDebugData();

        return $this;
    }

    /**
     * Determines method's availability based on config data and quote amount
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) &&
            $this->getConfigHelper()->isMethodAvailable();
    }

    // /**
    //  * Checks base currency against the allowed currency
    //  *
    //  * @param string $currencyCode
    //  * @return bool
    //  */
    // public function canUseForCurrency($currencyCode)
    // {
    //     return $this->getModuleHelper()->isCurrencyAllowed(
    //         $this->getCode(),
    //         $currencyCode
    //     );
    // }
}
