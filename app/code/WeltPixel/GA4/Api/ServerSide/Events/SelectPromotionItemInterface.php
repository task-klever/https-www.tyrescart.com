<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SelectPromotionItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return SelectPromotionItemInterface
     */
    public function setParams($options);
}
