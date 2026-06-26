<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddShippingInfoItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return AddShippingInfoItemInterface
     */
    public function setParams($options);
}
