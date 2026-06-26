<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewCartInterface
{
    /**
     * @param $pageLocation
     * @return ViewCartInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return ViewCartInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return ViewCartInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return ViewCartInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return ViewCartInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return ViewCartInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return ViewCartInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return ViewCartInterface
     */
    public function setValue($value);

    /**
     * @param ViewCartItemInterface $viewCartItem
     * @return ViewCartInterface
     */
    public function addItem($viewCartItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
