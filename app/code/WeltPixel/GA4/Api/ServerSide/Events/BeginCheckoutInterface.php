<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface BeginCheckoutInterface
{
    /**
     * @param $pageLocation
     * @return BeginCheckoutInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return BeginCheckoutInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return BeginCheckoutInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return BeginCheckoutInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return BeginCheckoutInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return BeginCheckoutInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return BeginCheckoutInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return BeginCheckoutInterface
     */
    public function setValue($value);

    /**
     * @param $coupon
     * @return BeginCheckoutInterface
     */
    public function setCoupon($coupon);

    /**
     * @param BeginCheckoutItemInterface $beginCheckoutItem
     * @return BeginCheckoutInterface
     */
    public function addItem($beginCheckoutItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
