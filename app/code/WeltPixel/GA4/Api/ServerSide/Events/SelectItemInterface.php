<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

use WeltPixel\GA4\Model\ServerSide\Events\SelectItem;

interface SelectItemInterface
{
    /**
     * @param $pageLocation
     * @return SelectItemInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return SelectItemInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return SelectItemInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return SelectItemInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SelectItemInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return SelectItemInterface
     */
    public function setUserId($userId);

    /**
     * @param $listId
     * @return SelectItemInterface
     */
    public function setItemListId($listId);

    /**
     * @param $listName
     * @return SelectItemInterface
     */
    public function setItemListName($listName);

    /**
     * @param SelectItemItemInterface $selectItemItem
     * @return SelectItemInterface
     */
    public function addItem($selectItemItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
