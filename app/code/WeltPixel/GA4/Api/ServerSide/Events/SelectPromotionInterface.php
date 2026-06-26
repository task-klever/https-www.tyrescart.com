<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SelectPromotionInterface
{
    /**
     * @param $pageLocation
     * @return SelectPromotionInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return SelectPromotionInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return SelectPromotionInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return SelectPromotionInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SelectPromotionInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return SelectPromotionInterface
     */
    public function setUserId($userId);


    /**
     * @param $promotionId
     * @return SelectPromotionInterface
     */
    public function setPromotionId($promotionId);

    /**
     * @param $promotionName
     * @return SelectPromotionInterface
     */
    public function setPromotionName($promotionName);

    /**
     * @param $creativeSlot
     * @return SelectPromotionInterface
     */
    public function setCreativeSlot($creativeSlot);

    /**
     * @param $creativeName
     * @return SelectPromotionInterface
     */
    public function setCreativeName($creativeName);

    /**
     * @param SelectPromotionItemInterface $viewPromotionItem
     * @return SelectPromotionInterface
     */
    public function addItem($viewPromotionItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);
}
