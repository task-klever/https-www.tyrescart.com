<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddShippingInfoInterface
{
    /**
     * @param $pageLocation
     * @return AddShippingInfoInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return AddShippingInfoInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return AddShippingInfoInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return AddShippingInfoInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return AddShippingInfoInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return AddShippingInfoInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return AddShippingInfoInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return AddShippingInfoInterface
     */
    public function setValue($value);

    /**
     * @param $coupon
     * @return AddShippingInfoInterface
     */
    public function setCoupon($coupon);

    /**
     * @param $shippingTier
     * @return AddShippingInfoInterface
     */
    public function setShippingTier($shippingTier);

    /**
     * @param AddShippingInfoItemInterface $addShippingInfoItem
     * @return AddShippingInfoInterface
     */
    public function addItem($addShippingInfoItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
