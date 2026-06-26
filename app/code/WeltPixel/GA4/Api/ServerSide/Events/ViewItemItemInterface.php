<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return ViewItemItemInterface
     */
    public function setParams($options);
}
