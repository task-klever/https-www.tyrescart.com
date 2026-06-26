<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SignupInterface
{
    /**
     * @param $pageLocation
     * @return SignupInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return SignupInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return SignupInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return SignupInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SignupInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return SignupInterface
     */
    public function setUserId($userId);

    /**
     * @param $method
     * @return SignupInterface
     */
    function setMethod($method);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
