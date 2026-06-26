<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SelectPromotionBuilderInterface
{
    /**
     * @param $promotionId
     * @param $promotionName
     * @param $creativeName
     * @param $creativeSlot
     * @param $promoItemIds
     * @return null|SelectPromotionInterface
     */
    public function getSelectPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoItemIds);
}
