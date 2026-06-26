<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionInterface;
use WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionItemInterface;

class ViewPromotion implements ViewPromotionInterface
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
    protected $viewPromotionItems;

    /**
     * @var array
     */
    protected $viewPromotionEvent;

    public function __construct()
    {
        $this->viewPromotionEvent = [];
        $this->payloadData = [];
        $this->payloadData['events'] = [];
        $this->viewPromotionEvent['name'] = 'view_promotion';
        $this->eventParams = [];
        $this->viewPromotionItems = [];
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
        $this->eventParams['items'] = $this->viewPromotionItems;
        $this->viewPromotionEvent['params'] = $this->eventParams;

        array_push($this->payloadData['events'], $this->viewPromotionEvent);
        return $this->payloadData;
    }

    /**
     * @param $pageLocation
     * @return ViewPromotionInterface
     */
    public function setPageLocation($pageLocation)
    {
        $this->eventParams['page_location'] = (string)$pageLocation;
        return $this;
    }

    /**
     * @param $clientId
     * @return ViewPromotionInterface
     */
    public function setClientId($clientId)
    {
        $this->payloadData['client_id'] = (string)$clientId;
        return $this;
    }

    /**
     * @param $userProperties
     * @return ViewPromotionInterface
     */
    public function setUserProperties($userProperties)
    {
        $this->payloadData['user_properties'] = $userProperties;
        return $this;
    }

    /**
     * @param $sessionId
     * @return ViewPromotionInterface
     */
    public function setSessionId($sessionId)
    {
        $this->eventParams['session_id'] =(string)$sessionId;
        return $this;
    }

    /**
     * @param $timestamp
     * @return ViewPromotionInterface
     */
    public function setTimestamp($timestamp)
    {
        $this->payloadData['timestamp_micros'] = (string)$timestamp;
        return $this;
    }

    /**
     * @param $userId
     * @return ViewPromotionInterface
     */
    public function setUserId($userId)
    {
        $this->payloadData['user_id'] = (string)$userId;
        $this->payloadData['user_data'] = (object)[];
        return $this;
    }

    /**
     * @param $promotionId
     * @return ViewPromotionInterface
     */
    public function setPromotionId($promotionId)
    {
        $this->eventParams['promotion_id'] = $promotionId;
        return $this;
    }

    /**
     * @param $promotionName
     * @return ViewPromotionInterface
     */
    public function setPromotionName($promotionName)
    {
        $this->eventParams['promotion_name'] = $promotionName;
        return $this;
    }

    /**
     * @param $creativeSlot
     * @return ViewPromotionInterface
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
     * @param ViewPromotionItemInterface $viewPromotionItem
     * @return ViewPromotionInterface
     */
    public function addItem($viewPromotionItem)
    {
        $this->viewPromotionItems[] = $viewPromotionItem->getParams();
        return $this;
    }
}
