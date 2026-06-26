<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddToCartInterface
{
    /**
     * @param $pageLocation
     * @return AddToCartInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return AddToCartInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return AddToCartInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return AddToCartInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return AddToCartInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return AddToCartInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return AddToCartInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return AddToCartInterface
     */
    public function setValue($value);

    /**
     * @param AddToCartItemInterface $addToCartItem
     * @return AddToCartInterface
     */
    public function addItem($addToCartItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
