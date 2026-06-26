<?php

namespace Meetanshi\OrderNumber\Plugin\Model\Quote;

use Magento\Quote\Model\ResourceModel\Quote;
use Meetanshi\OrderNumber\Helper\Data;
use Magento\Sales\Model\Order;

/**
 * Class ReserveOrder
 * @package Meetanshi\OrderNumber\Plugin\Model\Quote
 */
class ReserveOrder
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ReserveOrder constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Quote $subject
     * @param \Closure $proceed
     * @param $quote
     * @return mixed|string|string[]
     */
    public function aroundGetReservedOrderId(Quote $subject, \Closure $proceed, $quote)
    {
        $sequence = $proceed($quote);
        $incrementId = $this->helper->prepareIncrementId($quote, Order::ENTITY, $sequence);
        return $incrementId;
    }
}
