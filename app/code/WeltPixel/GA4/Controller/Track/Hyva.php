<?php
namespace WeltPixel\GA4\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Hyva extends Action
{
    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $ga4Helper;


    /** @var \WeltPixel\GA4\Model\ServerSide\Api */
    protected $ga4ServerSideApi;

    /** @var \WeltPixel\GA4\Api\ServerSide\Events\AddShippingInfoBuilderInterface */
    protected $addShippingInfoBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @param Context $context
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper
     * @param \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi
     * @param \WeltPixel\GA4\Api\ServerSide\Events\AddShippingInfoBuilderInterface $addShippingInfoBuilder
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        Context $context,
        \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper,
        \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi,
        \WeltPixel\GA4\Api\ServerSide\Events\AddShippingInfoBuilderInterface $addShippingInfoBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        parent::__construct($context);
        $this->ga4Helper = $ga4Helper;
        $this->ga4ServerSideApi = $ga4ServerSideApi;
        $this->addShippingInfoBuilder = $addShippingInfoBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $step = $this->getRequest()->getPostValue('step');
        $option = $this->getRequest()->getPostValue('option');

        if (!$step || !$option) {
            return $this->prepareResult('');
        }

        $quote = $this->checkoutSession->getQuote();

        if (!$quote) {
            return $this->prepareResult('');
        }

        switch ($step) {
            case '1' :
                $shippingDescription = $quote->getShippingAddress()->getShippingDescription();

                if ($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_ADD_SHIPPING_INFO)) {
                    $addShippingInfoEvent = $this->addShippingInfoBuilder->getAddShippingInfoEvent($quote, $shippingDescription);
                    $this->ga4ServerSideApi->pushAddShippingInfoEvent($addShippingInfoEvent);
                }

                if (!($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_ADD_SHIPPING_INFO)
                    && $this->ga4Helper->isDataLayerEventDisabled())) {
                    $dataLayer = $this->ga4Helper->addCheckoutStepPushData('1', $shippingDescription);
                }
                break;
            case '2' :
                if (!($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_ADD_PAYMENT_INFO)
                    && $this->ga4Helper->isDataLayerEventDisabled())) {
                    $paymentCode = $quote->getPayment()->getMethod();
                    $allPaymentMethodsArray = $this->paymentHelper->getPaymentMethodList();
                    if (isset($allPaymentMethodsArray[$paymentCode])) {
                        $paymentMethodTitle = $allPaymentMethodsArray[$paymentCode];
                        $dataLayer = $this->ga4Helper->addCheckoutStepPushData('2', $paymentMethodTitle);
                    }
                }
                break;
        }

        if (isset($dataLayer[0])) {
            return $this->prepareResult($dataLayer[0]);
        }

        return $this->prepareResult('');
    }

    /**
     * @param array $result
     * @return string
     */
    protected function prepareResult($result)
    {
        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
