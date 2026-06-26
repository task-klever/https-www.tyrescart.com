<?php


namespace TotalPay\Gateway\Model\Ipn;

/**
 * Base IPN Handler Class
 *
 * Class AbstractIpn
 * @package TotalPay\Gateway\Model\Ipn
 */
abstract class AbstractIpn
{
    use \TotalPay\Gateway\Model\Traits\Logger;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $_context;
    /**
     * @var \Zend\Log\Logger
     */
    private $_logger;
    /**
     * @var \TotalPay\Gateway\Helper\Data
     */
    private $_moduleHelper;
    /**
     * @var \TotalPay\Gateway\Model\Config
     */
    private $_configHelper;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $_orderFactory;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $_orderSender;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $_creditMemoSender;

    /**
     * Get Payment Solution Code (used to create an instance of the Config Object)
     * @return string
     */
    abstract protected function getPaymentMethodCode();

    /**
     * Update / Create Transactions; Updates Order Status
     * @param \stdClass $responseObject
     * @return void
     */
    abstract protected function processNotification($responseObject);

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditMemoSender
     * @param \Psr\Log\LoggerInterface $logger
     * @param \TotalPay\Gateway\Helper\Data $moduleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditMemoSender,
        \Psr\Log\LoggerInterface $logger,
        \TotalPay\Gateway\Helper\Data $moduleHelper
    ) {
        $this->_context = $context;
        $this->_orderFactory = $orderFactory;
        $this->_orderSender = $orderSender;
        $this->_creditMemoSender = $creditMemoSender;
        $this->_logger = $this->_initLogger();
        $this->_moduleHelper = $moduleHelper;
        $this->_configHelper =
            $this->_moduleHelper->getMethodConfig(
                $this->getPaymentMethodCode()
            );
    }

    /**
     * @return \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    public function getOrderSender()
    {
        return $this->_orderSender;
    }

    /**
     *
     * @return null|string (null => failed; responseText => success)
     * @throws \Exception
     * @throws \TotalPayGateway\Exceptions\InvalidArgument
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handleTotalPayGatewayNotification($responseData)
    {
        $json = file_get_contents('php://input');
        error_log('IPN response:' . $json);
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/total_pay_ipn.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r($responseData, true));
        if (isset($responseData['id']) && isset($responseData['hash']) && isset($responseData['order_number'])) {

        } else {
            $this->_logger->debug("Error in params");

            return [
                'body' => 'Error in params',
                'code' => 422
            ];
        }

        $res = $this->checkTotalPayResponse($responseData);
        if ($res === true) {

            $this->setOrderByReconcile($responseData);
            //send order email for type = sale success
            if ($responseData['type'] == 'sale' && $responseData['status'] == 'success') {
                try {
                    $this->getOrder()->setData('totalpay_paid', 1);
                    $this->getOrderSender()->send($this->getOrder());
                    $this->getOrder()->setData('totalpay_paid', 0);

                } catch (\Throwable $e) {
                    $this->_logger->debug($e->getMessage());
                }
            }

            try {


                error_log('absipntrycatch');
                $this->processNotification($responseData);

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $comment = $this->createIpnComment(__('Exception in webhook processing: %1', $e->getMessage()), true);
                $comment->save();
                throw $e;
            }


        } else {
            return [
                'body' => $res,
                'code' => 403
            ];
        }
        //return array('body' => 'Forbidden','code' => 403);

        return [
            'body' => 'OK',
            'code' => 200
        ];

    }

    protected function checkTotalPayResponse($response)
    {
        $this->_logger->debug("checking parameters");
        $settings = [
            'merchant_key' => $this->getConfigHelper()->getShopId(),
            'merchant_pass' => $this->getConfigHelper()->getShopKey()
        ];

        $validated = $this->isPaymentValid($settings, $response);
        if ($validated === true) {
            $this->_logger->debug("Responce - OK");
            return true;
        } else {
            $this->_logger->debug($validated);
            return $validated;
        }
    }

    protected function isPaymentValid($totalpaySetting, $response)
    {
        if ($response['status'] == 'fail' && $response['type'] == 'sale') {
            return 'An error has occurred during payment. Order is declined.';
        }
        if ($response['status'] == 'fail' && $response['type'] == 'refund') {
            return 'An error has occurred during payment. Refund is declined.';
        }

        $responseHash = $response['hash'];

        if ($this->getSignature($response, $totalpaySetting['merchant_pass']) != $responseHash) {
            return 'An error has occurred during payment. Hash is not valid.';
        }
        return true;
    }

    public function getSignature($data, $password)
    {
        $hash_string = $data['id'] . $data['order_number'] . $data['order_amount'] . $data['order_currency'] . $data['order_description'] . $password;
        $hash = sha1(md5(strtoupper($hash_string)));
        return $hash;
    }

    /**
     * Load order
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        if (!isset($this->_order) || empty($this->_order->getId())) {
            throw new \Exception('IPN-Order is not set to an instance of an object');
        }

        return $this->_order;
    }

    /**
     * Get an Instance of the Magento Payment Object
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|mixed|null
     * @throws \Exception
     */
    protected function getPayment()
    {
        return $this->getOrder()->getPayment();
    }

    /**
     * Initializes the Order Object from the transaction in the Reconcile response object
     * @param $responseObject
     * @throws \Exception
     */
    private function setOrderByReconcile($responseObject)
    {

        [$incrementId, $hash] = explode('_', $responseObject['order_number']);

        $id = ltrim($incrementId, '0');
        //intva

        $this->_order = $this->getOrderFactory()->create()->loadByIncrementId(intval($id));

        if (!$this->_order->getId()) {
            error_log('AbstarctIPN: order:');
            throw new \Exception(sprintf('Wrong order ID: "%s".', $incrementId));

        } else {
            error_log('AbstarctIPN: order:' . $this->_order->getId());
        }
    }

    /**
     * Generate an "IPN" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param string|null $message
     * @param bool $addToHistory
     * @return string|\Magento\Sales\Model\Order\Status\History
     */
    protected function createIpnComment($message = null, $addToHistory = false)
    {
        if ($addToHistory && !empty($message)) {
            $message = $this->getOrder()->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
        return $message;
    }

    /**
     * Get an instance of the Module Config Helper Object
     * @return \TotalPay\Gateway\Model\Config
     */
    protected function getConfigHelper()
    {
        return $this->_configHelper;
    }

    /**
     * Get an instance of the Magento Action Context Object
     * @return \Magento\Framework\App\Action\Context
     */
    protected function getContext()
    {
        return $this->_context;
    }

    /**
     * Get an instance of the Magento Logger Interface
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Get an Instance of the Module Helper Object
     * @return \TotalPay\Gateway\Helper\Data
     */
    protected function getModuleHelper()
    {
        return $this->_moduleHelper;
    }

    /**
     * Get an Instance of the magento Order Factory Object
     * @return \Magento\Sales\Model\OrderFactory
     */
    protected function getOrderFactory()
    {
        return $this->_orderFactory;
    }

    /**
     * @param \stdClass $responseObject
     * @return bool
     */
    protected function getShouldSetCurrentTranPending($responseObject)
    {
        return
            !($responseObject['type'] == 'sale' && $responseObject['status'] == 'success');
    }

    // /**
    //  * @param \stdClass $responseObject
    //  * @return bool
    //  */
    // protected function getShouldCloseCurrentTransaction($responseObject)
    // {
    //     // $helper = $this->getModuleHelper();
    //     // $voidableTransactions = [
    //     //     $helper::AUTHORIZE
    //     // ];

    //     // /*
    //     //  *  It the last transaction is closed, it cannot be voided
    //     //  */
    //     // return !in_array($responseObject->getResponse()->transaction->type, $voidableTransactions);
    //     return false;
    // }
}
