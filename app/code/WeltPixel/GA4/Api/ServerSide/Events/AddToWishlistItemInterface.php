<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddToWishlistItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return AddToWishlistItemInterface
     */
    public function setParams($options);
}
