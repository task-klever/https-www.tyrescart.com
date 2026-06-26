<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddPaymentInfoInterface
{
    /**
     * @param $pageLocation
     * @return AddPaymentInfoInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return AddPaymentInfoInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return AddPaymentInfoInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return AddPaymentInfoInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return AddPaymentInfoInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return AddPaymentInfoInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return AddPaymentInfoInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return AddPaymentInfoInterface
     */
    public function setValue($value);

    /**
     * @param $coupon
     * @return AddPaymentInfoInterface
     */
    public function setCoupon($coupon);

    /**
     * @param $paymentType
     * @return AddPaymentInfoInterface
     */
    public function setPaymentType($paymentType);

    /**
     * @param AddPaymentInfoItemInterface $addPaymentInfoItem
     * @return AddPaymentInfoInterface
     */
    public function addItem($addPaymentInfoItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
