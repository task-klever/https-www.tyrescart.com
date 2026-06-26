<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface LoginInterface
{
    /**
     * @param $pageLocation
     * @return LoginInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return LoginInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return LoginInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return LoginInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return LoginInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return LoginInterface
     */
    public function setUserId($userId);

    /**
     * @param $method
     * @return LoginInterface
     */
    public function setMethod($method);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
