<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemInterface
{
    /**
     * @param $pageLocation
     * @return ViewItemInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return ViewItemInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return ViewItemInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return ViewItemInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return ViewItemInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return ViewItemInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return ViewItemInterface
     */
    public function setCurrency($currency);

    /**
     * @param $value
     * @return ViewItemInterface
     */
    public function setValue($value);

    /**
     * @param ViewItemItemInterface $viewItemItem
     * @return ViewItemInterface
     */
    public function addItem($viewItemItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
