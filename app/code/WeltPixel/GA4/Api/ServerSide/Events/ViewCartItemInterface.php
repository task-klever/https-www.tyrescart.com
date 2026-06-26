<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewCartItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return ViewCartItemInterface
     */
    public function setParams($options);
}
