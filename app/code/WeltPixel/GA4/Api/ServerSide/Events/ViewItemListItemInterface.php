<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemListItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return ViewItemListItemInterface
     */
    public function setParams($options);
}
