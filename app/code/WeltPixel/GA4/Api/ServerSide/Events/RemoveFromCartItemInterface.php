<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface RemoveFromCartItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return RemoveFromCartItemInterface
     */
    public function setParams($options);
}
