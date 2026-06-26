<?php

namespace WeltPixel\GA4\Cron;

/**
 * Class PurchasePushServerSide
 */
class PurchasePushServerSide
{
    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $ga4Helper;

    /** @var \WeltPixel\GA4\Model\ServerSide\Api */
    protected $ga4ServerSideApi;

    /** @var \WeltPixel\GA4\Api\ServerSide\Events\PurchaseBuilderInterface */
    protected $purchaseBuilder;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper
     * @param \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi
     * @param \WeltPixel\GA4\Api\ServerSide\Events\PurchaseBuilderInterface $purchaseBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper,
        \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi,
        \WeltPixel\GA4\Api\ServerSide\Events\PurchaseBuilderInterface $purchaseBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->ga4Helper = $ga4Helper;
        $this->ga4ServerSideApi = $ga4ServerSideApi;
        $this->purchaseBuilder = $purchaseBuilder;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        if (!$this->ga4Helper->isServerSideTrakingEnabled()) {
            return $this;
        }

        if ($this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_PURCHASE)) {
            $orderIds = $this->purchaseBuilder->getMeasurementMissedOrderIds();
            foreach ($orderIds as $orderId) {
                $order = $this->orderRepository->get($orderId);
                if ($order && $this->isFreeOrderTrackingAllowedForGoogleAnalytics($order) &&  $this->ga4Helper->isOrderTrackingAllowedBasedOnOrderStatus($order)) {
                    $purchaseEvent = $this->purchaseBuilder->getPurchaseEvent($order, true);
                    $this->ga4ServerSideApi->pushPurchaseEvent($purchaseEvent);
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isFreeOrderTrackingAllowedForGoogleAnalytics($order)
    {
        $excludeFreeOrder = $this->ga4Helper->excludeFreeOrderFromPurchaseForGoogleAnalytics();
        return $this->isFreeOrderAllowed($order, $excludeFreeOrder);
    }

    /**
     * @param $order
     * @param bool $excludeFreeOrder
     * @return bool
     */
    protected function isFreeOrderAllowed($order, $excludeFreeOrder)
    {
        if (!$excludeFreeOrder) {
            return true;
        }

        $orderTotal = $order->getGrandtotal();
        if ($orderTotal > 0) {
            return true;
        }

        return false;
    }

}
