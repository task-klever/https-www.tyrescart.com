<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddShippingInfoBuilderInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $shippingTier
     * @return null|AddShippingInfoInterface
     */
    public function getAddShippingInfoEvent($quote, $shippingTier);
}
