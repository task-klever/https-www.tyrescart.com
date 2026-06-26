<?php
namespace WeltPixel\GA4\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Promotionview extends Action
{
    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $ga4Helper;

    /** @var \WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionBuilderInterface */
    protected $promotionViewBuilder;

    /** @var \WeltPixel\GA4\Model\ServerSide\Api */
    protected $ga4ServerSideApi;

    /**
     * @param Context $context
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper
     * @param \WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionBuilderInterface $promotionViewBuilder
     * @param \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi
     */
    public function __construct(
        Context $context,
        \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper,
        \WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionBuilderInterface $promotionViewBuilder,
        \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi
    ) {
        parent::__construct($context);
        $this->ga4Helper = $ga4Helper;
        $this->promotionViewBuilder = $promotionViewBuilder;
        $this->ga4ServerSideApi = $ga4ServerSideApi;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $promotionViews = $this->getRequest()->getPostValue('promotion_views');
        $dataLayerPush = $this->getRequest()->getPostValue('dataLayerPush', false);
        $responseData = [];

        if (!$promotionViews || !is_array($promotionViews)) {
            return $this->prepareResult('');
        }

        if ($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_VIEW_PROMOTION)) {
            foreach ($promotionViews as $promotionView) {
                $promotionId = $promotionView['promotion_id'] ?? '';
                $promotionName = $promotionView['promotion_name'] ?? '';
                $creativeName = $promotionView['creative_name'] ?? '';
                $creativeSlot = $promotionView['creative_slot'] ?? '';
                $promoItemIds = $promotionView['product_ids'] ?? '';

                $promoProductIds = explode(',', $promoItemIds ?? '');

                $viewPromotionEvent = $this->promotionViewBuilder->getViewPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoProductIds);
                $this->ga4ServerSideApi->pushViewPromotionEvent($viewPromotionEvent);
            }
        }

        if ($dataLayerPush && !($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_VIEW_PROMOTION)
            && $this->ga4Helper->isDataLayerEventDisabled())) {

            foreach ($promotionViews as $promotionView) {
                $promotionId = $promotionView['promotion_id'];
                $promotionName = $promotionView['promotion_name'];
                $creativeName = $promotionView['creative_name'];
                $creativeSlot = $promotionView['creative_slot'];
                $promoItemIds = $promotionView['product_ids'];
                $promoItemUniqueId = $promotionView['promo_items_uniqueid'] ?? false;

                if (!$promotionId || !$promotionName || !$creativeName || !$creativeSlot) {
                    continue;
                }

                $promoProductIds = explode(',', $promoItemIds ?? '');
                $viewPromotionEvent = $this->promotionViewBuilder->getViewPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoProductIds);

                //check if frontend promotion view is enabled
                $viewPromotionEventData = $viewPromotionEvent->getParams();
                if (!$promoItemUniqueId && $viewPromotionEventData && isset($viewPromotionEventData['events'])) {
                    $ecommerceData = $viewPromotionEventData['events'][0]['params'];
                    unset($ecommerceData['page_location']);

                    $result = [
                        'ecommerce' => $ecommerceData,
                        'event' => 'view_promotion'
                    ];
                    $responseData[] = $result;
                }
            }
        }

        return $this->prepareResult($responseData);
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
