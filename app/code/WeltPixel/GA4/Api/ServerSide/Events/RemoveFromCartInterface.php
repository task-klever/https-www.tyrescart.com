<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface RemoveFromCartInterface
{
    /**
     * @param $pageLocation
     * @return RemoveFromCartInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return RemoveFromCartInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return RemoveFromCartInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return RemoveFromCartInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return RemoveFromCartInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return RemoveFromCartInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return RemoveFromCartInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return RemoveFromCartInterface
     */
    public function setValue($value);

    /**
     * @param RemoveFromCartItemInterface $removeFromCartItem
     * @return RemoveFromCartInterface
     */
    public function addItem($removeFromCartItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
