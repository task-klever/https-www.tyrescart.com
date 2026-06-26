<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewPromotionItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return ViewPromotionItemInterface
     */
    public function setParams($options);
}
