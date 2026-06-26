<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewPromotionInterface
{
    /**
     * @param $pageLocation
     * @return ViewPromotionInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return ViewPromotionInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return ViewPromotionInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return ViewPromotionInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return ViewPromotionInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return ViewPromotionInterface
     */
    public function setUserId($userId);


    /**
     * @param $promotionId
     * @return ViewPromotionInterface
     */
    public function setPromotionId($promotionId);

    /**
     * @param $promotionName
     * @return ViewPromotionInterface
     */
    public function setPromotionName($promotionName);

    /**
     * @param $creativeSlot
     * @return ViewPromotionInterface
     */
    public function setCreativeSlot($creativeSlot);

    /**
     * @param $creativeName
     * @return ViewPromotionInterface
     */
    public function setCreativeName($creativeName);

    /**
     * @param ViewPromotionItemInterface $viewPromotionItem
     * @return ViewPromotionInterface
     */
    public function addItem($viewPromotionItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
