<?php

namespace TotalPay\Gateway\Model\Config\Source\Order\Status;

/**
 * Order Statuses source model
 * Class NewStatus
 * @package TotalPay\Gateway\Model\Config\Source\Order\Status
 */
class NewStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
}
