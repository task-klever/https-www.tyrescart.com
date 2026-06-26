<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface BeginCheckoutItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return BeginCheckoutItemInterface
     */
    public function setParams($options);
}
