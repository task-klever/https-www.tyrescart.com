<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SelectItemItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return SelectItemItemInterface
     */
    public function setParams($options);
}
