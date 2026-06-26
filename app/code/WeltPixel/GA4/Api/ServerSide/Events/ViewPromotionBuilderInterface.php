<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewPromotionBuilderInterface
{
    /**
     * @param $promotionId
     * @param $promotionName
     * @param $creativeName
     * @param $creativeSlot
     * @param $promoItemIds
     * @return null|ViewPromotionInterface
     */
    public function getViewPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoItemIds);
}
