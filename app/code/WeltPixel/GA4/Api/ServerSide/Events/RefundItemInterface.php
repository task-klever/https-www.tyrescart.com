<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface RefundItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return RefundItemInterface
     */
    public function setParams($options);
}
