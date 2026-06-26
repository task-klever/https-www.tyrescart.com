<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionInterface;
use WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionItemInterface;

class SelectPromotion implements SelectPromotionInterface
{
    /**
     * @var array
     */
    protected $payloadData;

    /**
     * @var array
     */
    protected $eventParams;

    /**
     * @var array
     */
    protected $selectPromotionItems;

    /**
     * @var array
     */
    protected $selectPromotionEvent;

    public function __construct()
    {
        $this->selectPromotionEvent = [];
        $this->payloadData = [];
        $this->payloadData['events'] = [];
        $this->selectPromotionEvent['name'] = 'select_promotion';
        $this->eventParams = [];
        $this->selectPromotionItems = [];
    }

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false)
    {
        if ($debugMode) {
            $this->eventParams['debug_mode'] = 1;
        }
        $this->eventParams['items'] = $this->selectPromotionItems;
        $this->selectPromotionEvent['params'] = $this->eventParams;

        array_push($this->payloadData['events'], $this->selectPromotionEvent);
        return $this->payloadData;
    }

    /**
     * @param $pageLocation
     * @return SeelectPromotionInterface
     */
    public function setPageLocation($pageLocation)
    {
        $this->eventParams['page_location'] = (string)$pageLocation;
        return $this;
    }

    /**
     * @param $clientId
     * @return SeelectPromotionInterface
     */
    public function setClientId($clientId)
    {
        $this->payloadData['client_id'] = (string)$clientId;
        return $this;
    }

    /**
     * @param $userProperties
     * @return SeelectPromotionInterface
     */
    public function setUserProperties($userProperties)
    {
        $this->payloadData['user_properties'] = $userProperties;
        return $this;
    }

    /**
     * @param $sessionId
     * @return SeelectPromotionInterface
     */
    public function setSessionId($sessionId)
    {
        $this->eventParams['session_id'] =(string)$sessionId;
        return $this;
    }

    /**
     * @param $timestamp
     * @return SeelectPromotionInterface
     */
    public function setTimestamp($timestamp)
    {
        $this->payloadData['timestamp_micros'] = (string)$timestamp;
        return $this;
    }

    /**
     * @param $userId
     * @return SeelectPromotionInterface
     */
    public function setUserId($userId)
    {
        $this->payloadData['user_id'] = (string)$userId;
        $this->payloadData['user_data'] = (object)[];
        return $this;
    }

    /**
     * @param $promotionId
     * @return SeelectPromotionInterface
     */
    public function setPromotionId($promotionId)
    {
        $this->eventParams['promotion_id'] = $promotionId;
        return $this;
    }

    /**
     * @param $promotionName
     * @return SeelectPromotionInterface
     */
    public function setPromotionName($promotionName)
    {
        $this->eventParams['promotion_name'] = $promotionName;
        return $this;
    }

    /**
     * @param $creativeSlot
     * @return SeelectPromotionInterface
     */
    public function setCreativeSlot($creativeSlot)
    {
        $this->eventParams['creative_slot'] = $creativeSlot;
        return $this;
    }

    /**
     * @param $creativeName
     * @return ViewPromotionInterface
     */
    public function setCreativeName($creativeName)
    {
        $this->eventParams['creative_name'] = $creativeName;
        return $this;
    }

    /**
     * @param SelectPromotionItemInterface $selectPromotionItem
     * @return SeelectPromotionInterface
     */
    public function addItem($selectPromotionItem)
    {
        $this->selectPromotionItems[] = $selectPromotionItem->getParams();
        return $this;
    }
}
