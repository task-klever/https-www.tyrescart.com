<?php
namespace WeltPixel\GA4\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Promotionclick extends Action
{
    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $ga4Helper;

    /** @var \WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionBuilderInterface */
    protected $promotionSelectBuilder;

    /** @var \WeltPixel\GA4\Model\ServerSide\Api */
    protected $ga4ServerSideApi;

    /**
     * @param Context $context
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper
     * @param \WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionBuilderInterface $promotionSelectBuilder
     * @param \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi
     */
    public function __construct(
        Context $context,
        \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper,
        \WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionBuilderInterface $promotionSelectBuilder,
        \WeltPixel\GA4\Model\ServerSide\Api $ga4ServerSideApi
    ) {
        parent::__construct($context);
        $this->ga4Helper = $ga4Helper;
        $this->promotionSelectBuilder = $promotionSelectBuilder;
        $this->ga4ServerSideApi = $ga4ServerSideApi;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $promotionId = $this->getRequest()->getPostValue('promotion_id', '');
        $promotionName = $this->getRequest()->getPostValue('promotion_name', '');
        $creativeName = $this->getRequest()->getPostValue('creative_name', '');
        $creativeSlot = $this->getRequest()->getPostValue('creative_slot', '');
        $promoItemIds = $this->getRequest()->getPostValue('product_ids');
        $dataLayerPush = $this->getRequest()->getPostValue('dataLayerPush', false);
        $promoItemUniqueId =  $this->getRequest()->getPostValue('promo_items_uniqueid', false);

        $responseData = [];

        if ($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_SELECT_PROMOTION)) {
            $promoProductIds = explode(',', $promoItemIds ?? '');
            $selectPromotionEvent = $this->promotionSelectBuilder->getSelectPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoProductIds);
            $this->ga4ServerSideApi->pushSelectPromotionEvent($selectPromotionEvent);
        }

        if (!$promoItemUniqueId && $dataLayerPush && !($this->ga4Helper->isServerSideTrakingEnabled() && $this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_SELECT_PROMOTION)
            && $this->ga4Helper->isDataLayerEventDisabled())) {

            $promoProductIds = explode(',', $promoItemIds ?? '');
            $selectPromotionEvent = $this->promotionSelectBuilder->getSelectPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoProductIds);
            $selectPromotionEventData = $selectPromotionEvent->getParams();
            if ($selectPromotionEventData && isset($selectPromotionEventData['events'])) {
                $ecommerceData = $selectPromotionEventData['events'][0]['params'];
                unset($ecommerceData['page_location']);

                $result = [
                    'ecommerce' => $ecommerceData,
                    'event' => 'select_promotion'
                ];
                $responseData[] = $result;
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
