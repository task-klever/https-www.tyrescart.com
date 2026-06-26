<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface RefundInterface
{
    /**
     * @param $pageLocation
     * @return RefundInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return RefundInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return RefundInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return RefundInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return RefundInterface
     */
    public function setTimestamp($timestamp);


    /**
     * @param $userId
     * @return RefundInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return RefundInterface
     */
    public function setCurrency($currency);

    /**
     * @param $transactionId
     * @return RefundInterface
     */
    public function setTransactionId($transactionId);

    /**
     * @param $value
     * @return RefundInterface
     */
    public function setValue($value);

    /**
     * @param $coupon
     * @return RefundInterface
     */
    public function setCoupon($coupon);

    /**
     * @param $shipping
     * @return RefundInterface
     */
    public function setShipping($shipping);

    /**
     * @param $tax
     * @return RefundInterface
     */
    public function setTax($tax);

    /**
     * @param RefundItemInterface $refundItem
     * @return RefundInterface
     */
    public function addItem($refundItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
