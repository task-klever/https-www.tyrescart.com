<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddPaymentInfoItemInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @param array $options
     * @return AddPaymentInfoItemInterface
     */
    public function setParams($options);
}
