<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface AddToWishlistInterface
{
    /**
     * @param $pageLocation
     * @return AddToWishlistInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return AddToWishlistInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return AddToWishlistInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return AddToWishlistInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return AddToWishlistInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return AddToWishlistInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return AddToWishlistInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return AddToWishlistInterface
     */
    public function setValue($value);

    /**
     * @param AddToWishlistItemInterface $addToCartItem
     * @return AddToWishlistInterface
     */
    public function addItem($addToCartItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
