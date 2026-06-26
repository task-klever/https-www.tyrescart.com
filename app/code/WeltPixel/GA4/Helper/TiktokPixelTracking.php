<?php

namespace WeltPixel\GA4\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TiktokPixelTracking extends Data
{
    /**
     * @return boolean
     */
    public function isTiktokPixelTrackingEnabled() {
        return $this->_tiktokPixelOptions['general_tracking']['enable'];
    }

    /**
     * @return string
     */
    public function getTiktokPixelTrackingCodeSnippet() {
        return trim($this->_tiktokPixelOptions['general_tracking']['code_snippet'] ?? '');
    }


    /**
     * @return array
     */
    public function getTiktokPixelTrackedEvents() {
        $trackedEvents = $this->_tiktokPixelOptions['general_tracking']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @return boolean
     */
    public function isServerSideTrackingEnabled()
    {
        if (!$this->_moduleManager->isEnabled('WeltPixel_GA4TikTokSS')) {
            return false;
        }
        $isServerSideTrackingEnabled = $this->_tiktokPixelOptions['serverside_tracking']['enable'];
        if (empty($isServerSideTrackingEnabled)) {
            return false;
        }
        return $isServerSideTrackingEnabled;
    }

    /**
     * @return boolean
     */
    public function enableTiktokPixelFrontendEventSending()
    {
        return $this->_tiktokPixelOptions['serverside_tracking']['enable_frontend_event_sending'] ?? true;
    }

    /**
     * @return array
     */
    public function getTikTokServerSideTrackedEvents() {
        $trackedEvents = $this->_tiktokPixelOptions['serverside_tracking']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldTikTokServerSideEventBeTracked($eventName) {
        $availableEvents = $this->getTikTokServerSideTrackedEvents();
        return in_array($eventName, $availableEvents);
    }

    /**
     * @return string
     */
    public function getTikTokPixelId() {
        return trim($this->_tiktokPixelOptions['serverside_tracking']['tiktokpixel_id'] ?? '');
    }

    /**
     * @return boolean
     */
    public function isTikTokPixelTestModeEnabled()
    {
        return $this->_tiktokPixelOptions['serverside_tracking']['enable_test_mode'] ?? false;
    }

    /**
     * @return string
     */
    public function getTikTokPixelTestEventCode() {
        return trim($this->_tiktokPixelOptions['serverside_tracking']['test_event_code'] ?? '');
    }

    /**
     * @return string
     */
    public function getTikTokPixelEventsApiKey() {
        return trim($this->_tiktokPixelOptions['serverside_tracking']['tiktok_events_api_key'] ?? '');
    }

    /**
     * @return string
     */
    public function getTikTokSSTrackUrl()
    {
        return $this->_getUrl('wpx_tiktok/pixel/tracker');
    }


    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldTiktokPixelEventBeTracked($eventName) {
        $enableFrontendEventSending = true;
        $serverSideTrackingEnabled = $this->isServerSideTrackingEnabled();

        if ($serverSideTrackingEnabled) {
            $enableFrontendEventSending = $this->enableTiktokPixelFrontendEventSending();
        }

        $availableEvents = $this->getTiktokPixelTrackedEvents();
        return in_array($eventName, $availableEvents) && $enableFrontendEventSending;
    }

    /**
     * @param $product
     * @return array
     */
    public function tiktokPixelAddToWishlistPushData($product)
    {
        $result = [
            'eventName' => \WeltPixel\GA4\Model\Config\Source\TiktokPixel\TrackingEvents::EVENT_ADD_TO_WISHLIST,
            'eventData' => [
                'value' => floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', '')),
                'currency' => $this->getCurrencyCode(),
                'contents' => [
                    [
                        'content_id' => $this->getTiktokProductId($product),
                        'content_type' => 'product',
                        'content_name' => addslashes(str_replace('"','&quot;', html_entity_decode($product->getName() ?? '')))
                    ]
                ]
            ],
            'additionalParams' => [
                'event_id' => $this->getAddToWishlistEventUID()
            ]
        ];

        return $result;
    }

    /**
     * @param $product
     * @param int $qty
     * @return array
     */
    public function tiktokPixelAddToCartPushData($product, $qty = 1)
    {
        $result = [
            'eventName' => \WeltPixel\GA4\Model\Config\Source\TiktokPixel\TrackingEvents::EVENT_ADD_TO_CART,
            'eventData' => [
                'value' => floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', '')),
                'currency' => $this->getCurrencyCode(),
                'quantity' => $qty,
                'contents' => [
                    [
                        'content_id' => $this->getTiktokProductId($product),
                        'content_type' => 'product',
                        'quantity' => $qty,
                        'content_name' => addslashes(str_replace('"','&quot;', html_entity_decode($product->getName() ?? '')))
                    ]
                ]
            ],
            'additionalParams' => [
                'event_id' => $this->getAddToCartEventUID()
            ]
        ];

        return $result;
    }

    /**
     * Returns the product id or sku based on the backend settings
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getTiktokProductId($product)
    {
        $idOption = $this->_tiktokPixelOptions['general_tracking']['id_selection'];
        $tikTokProductId = '';

        switch ($idOption) {
            case 'sku':
                $tikTokProductId = $product->getData('sku');
                break;
            case 'id':
            default:
            $tikTokProductId = $product->getId();
                break;
        }

        return $tikTokProductId;
    }

    /**
     * @return string
     */
    public function getEventUID()
    {
        $randomString = [];
        for ($i=1; $i<3; $i++) {
            $randomString[] = substr(md5(mt_rand()), 0, 8);
        }

        return implode('_', $randomString);
    }

    /**
     * @return string
     */
    public function getAddToWishlistEventUID() {
        if (!$this->registry->registry('tiktokss_add_to_wishlist_event_uid')) {
            $this->registry->register('tiktokss_add_to_wishlist_event_uid', $this->getEventUID());
        }

        return $this->registry->registry('tiktokss_add_to_wishlist_event_uid');
    }

    /**
     * @return string
     */
    public function getAddToCartEventUID() {
        if (!$this->registry->registry('tiktokss_add_to_cart_event_uid')) {
            $this->registry->register('tiktokss_add_to_cart_event_uid', $this->getEventUID());
        }

        return $this->registry->registry('tiktokss_add_to_cart_event_uid');
    }

    /**
     * @return mixed
     */
    public function getStoreCurrenUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl(false);
    }

    /**
     * @param array $categoryIds
     * @return string
     */
    public function getContentCategory($categoryIds)
    {
        $categoriesArray = $this->getGA4CategoriesFromCategoryIds($categoryIds);
        return implode(", ", $categoriesArray);
    }

}
