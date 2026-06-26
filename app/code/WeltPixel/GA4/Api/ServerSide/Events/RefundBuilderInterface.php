<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface RefundBuilderInterface
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo|int $creditmemo
     * @return null|RefundInterface
     */
    public function getRefundEvent($creditmemo);
}
