<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface PurchaseInterface
{
    /**
     * @param $pageLocation
     * @return PurchaseInterface
     */
    public function setPageLocation($pageLocation);

    /**
     * @param $clientId
     * @return PurchaseInterface
     */
    public function setClientId($clientId);

    /**
     * @param $userProperties
     * @return PurchaseInterface
     */
    public function setUserProperties($userProperties);

    /**
     * @param $sessionId
     * @return PurchaseInterface
     */
    public function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return PurchaseInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return PurchaseInterface
     */
    public function setUserId($userId);

    /**
     * @param $currency
     * @return PurchaseInterface
     */
    public function setCurrency($currency);

    /**
     * @param $transactionId
     * @return PurchaseInterface
     */
    public function setTransactionId($transactionId);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param $orderId
     * @return PurchaseInterface
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getOrderId();


    /**
     * @param $value
     * @return PurchaseInterface
     */
    public function setValue($value);

    /**
     * @param $coupon
     * @return PurchaseInterface
     */
    public function setCoupon($coupon);

    /**
     * @param $shipping
     * @return PurchaseInterface
     */
    public function setShipping($shipping);

    /**
     * @param $tax
     * @return PurchaseInterface
     */
    public function setTax($tax);

    /**
     * @param PurchaseItemInterface $purchaseItem
     * @return PurchaseInterface
     */
    public function addItem($purchaseItem);

    /**
     * @param bool $debugMode
     * @return array
     */
    public function getParams($debugMode = false);

    /**
     * @return void
     */
    public function markAsPushed();

    /**
     * @return boolean
     */
    public function isPushed();

    /**
     * @return integer
     */
    public function getStoreId();

    /**
     * @param $storeId
     * @return PurchaseInterface
     */
    public function setStoreId($storeId);
}
