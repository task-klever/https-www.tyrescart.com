<?php

namespace TotalPay\Gateway\Model\Traits;

/**
 * Trait for defining common variables and methods for all Payment Solutions
 * Trait OnlinePaymentMethod
 * @package TotalPay\Gateway\Model\Traits
 */
trait OnlinePaymentMethod
{
    /**
     * @var \TotalPay\Gateway\Model\Config
     */
    protected $_configHelper;
    /**
     * @var \TotalPay\Gateway\Helper\Data
     */
    protected $_moduleHelper;
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_actionContext;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface
     */
    protected $_transactionManager;

    /**
     * Get an Instance of the Config Helper Object
     * @return \TotalPay\Gateway\Model\Config
     */
    protected function getConfigHelper()
    {
        return $this->_configHelper;
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
     * Get an Instance of the Magento Action Context
     * @return \Magento\Framework\App\Action\Context
     */
    protected function getActionContext()
    {
        return $this->_actionContext;
    }

    /**
     * Get an Instance of the Magento Core Message Manager
     * @return \Magento\Framework\Message\ManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->getActionContext()->getMessageManager();
    }

    /**
     * Get an Instance of Magento Core Store Manager Object
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManager()
    {
        return$this->_storeManager;
    }

    /**
     * Get an Instance of the Url
     * @return \Magento\Framework\UrlInterface
     */
    protected function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * Get an Instance of the Magento Core Checkout Session
     * @return \Magento\Checkout\Model\Session
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get an Instance of the Magento Transaction Manager
     * @return \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface
     */
    protected function getTransactionManager()
    {
        return $this->_transactionManager;
    }



    /**
     * Base Payment Refund Method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param \Magento\Sales\Model\Order\Payment\Transaction|null $captureTransaction
     * @return $this
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function doRefund(\Magento\Payment\Model\InfoInterface $payment, $amount, $captureTransaction)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $helper = $this->getModuleHelper();
        // if (!$this->getModuleHelper()->canRefundTransaction($captureTransaction)) {
        //     $errorMessage = __('Order cannot be refunded online.');

        //     $this->getMessageManager()->addError($errorMessage);
        //     $this->getModuleHelper()->throwWebApiException($errorMessage);
        // }

        $hash = sha1(md5(strtoupper($captureTransaction->getTxnId() . number_format($amount, 2, '.', '') . $this->getConfigHelper()->getShopKey())));

        $data = array(
            'merchant_key' => $this->getConfigHelper()->getShopId(),
            'payment_id'   => $captureTransaction->getTxnId(),
            'amount'       => number_format($amount, 2, '.', ''),
            'hash'         => $hash
        );


        $responseArr = $this->doRequest($data, $this->getConfigHelper()->getDomainGateway());

        // $this->_logger
        // error_log($responseArr);
        $responseArr = json_decode($responseArr,true);



        if (isset($responseArr['result'])) {
            if ($responseArr['result'] == 'accepted') {
                $this->getMessageManager()->addSuccess('Refund accepted');
            }
        }else{
            foreach($responseArr['errors'] as $mes){
                $this->getMessageManager()->addError($mes['error_message']);
            }
        }

        unset($data);

        return $this;
    }




    private function doRequest($data,$url)
    {
        try {
            $httpHeaders = new \Laminas\Http\Headers();
            $httpHeaders->addHeaders([
                'User-Agent' => 'Magento 2 CMS',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]);
            $request = new \Laminas\Http\Request();
            $request->setHeaders($httpHeaders);

            $request->setUri($url);

            $request->setMethod(\Laminas\Http\Request::METHOD_POST);

            $params = json_encode($data);
            $request->setContent($params);

            $client = new \Laminas\Http\Client();
            $options = [
                'adapter' => 'Laminas\Http\Client\Adapter\Curl',
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
                'maxredirects' => 1,
                'timeout' => 30
            ];
            $client->setOptions($options);

            $response = $client->send($request);

            error_log("doRequest: response body". $response->getBody());

            return $response->getBody();

        } catch (\Exception $e) {
            error_log("doRequest: exception");
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }
    }
}
