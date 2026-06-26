<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemListInterface
{
    /**
     * @param $pageLocation
     * @return ViewItemListInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return ViewItemListInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return ViewItemListInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return ViewItemListInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return ViewItemListInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return ViewItemListInterface
     */
    public function setUserId($userId);

    /**
     * @param $listId
     * @return ViewItemListInterface
     */
    public function setItemListId($listId);

    /**
     * @param $listName
     * @return ViewItemListInterface
     */
    public function setItemListName($listName);

    /**
     * @param ViewItemListItemInterface $viewItemListItem
     * @return ViewItemListInterface
     */
    public function addItem($viewItemListItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
