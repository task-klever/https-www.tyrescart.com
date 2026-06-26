<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SearchInterface
{
    /**
     * @param $pageLocation
     * @return SearchInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return SearchInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return SearchInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return SearchInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SearchInterface
     */
    public function setTimestamp($timestamp);


    /**
     * @param $userId
     * @return SearchInterface
     */
    public function setUserId($userId);

    /**
     * @param $searchTerm
     * @return SearchInterface
     */
    public function setSearchTerm($searchTerm);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
