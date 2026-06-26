<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface PurchaseBuilderInterface
{
    /**
     * @param $order
     * @param boolean
     * @return null|PurchaseInterface
     */
    public function getPurchaseEvent($order, $isAdmin = false);

    /**
     * @return array
     */
    public function getMeasurementMissedOrderIds();
}
