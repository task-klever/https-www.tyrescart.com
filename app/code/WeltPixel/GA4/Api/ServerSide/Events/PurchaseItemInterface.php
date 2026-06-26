<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface PurchaseItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return PurchaseItemInterface
     */
    public function setParams($options);
}
