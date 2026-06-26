<?php

namespace TotalPay\Gateway\Api\Data;

/**
 * Interface PaymentInterface
 * @package TotalPay\Gateway\Api\Data
 */
interface PaymentInterface
{
    const TYPE = 'object';

    const ORDER_ID = 'order_id';

    const REDIRECT_TOTALPAY_URL = 'redirect_url';

    /**
     * @param int $value
     * @return self
     * @api
     */
    public function setOrderId($value);

    /**
     * @return string
     * @api
     */
    public function getObject();

    /**
     * @return int
     * @api
     */
    public function getOrderId();

    /**
     * @return string|null
     * @api
     *
     */
    public function getRedirectPaymentUrl();

    /**
     * @param string $value
     *
     * @return self
     * @api
     *
     */
    public function setRedirectPaymentUrl($value);

}
