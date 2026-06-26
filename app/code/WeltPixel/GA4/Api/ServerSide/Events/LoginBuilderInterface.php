<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface LoginBuilderInterface
{
    /**
     * @param $customerId
     * @return null|LoginInterface
     */
    public function getLoginEvent($customerId);
}
