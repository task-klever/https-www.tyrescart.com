<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddToCartItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return AddToCartItemInterface
     */
    public function setParams($options);
}
