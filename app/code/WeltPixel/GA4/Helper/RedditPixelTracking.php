<?php

namespace WeltPixel\GA4\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedditPixelTracking extends Data
{
    /**
     * @return boolean
     */
    public function isRedditPixelTrackingEnabled() {
        return $this->_redditPixelOptions['general_tracking']['enable'];
    }

    /**
     * @return string
     */
    public function getRedditPixelCodeSnippet() {
        return trim($this->_redditPixelOptions['general_tracking']['code_snippet'] ?? '');
    }


    /**
     * @return array
     */
    public function getRedditPixelTrackedEvents() {
        $trackedEvents = $this->_redditPixelOptions['general_tracking']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldRedditPixelEventBeTracked($eventName) {
        $enableFrontendEventSending = true;
        $serverSideTrackingEnabled = $this->isServerSideTrackingEnabled();

        if ($serverSideTrackingEnabled) {
            $enableFrontendEventSending = $this->enableRedditPixelFrontendEventSending();
        }

        $availableEvents = $this->getRedditPixelTrackedEvents();
        return in_array($eventName, $availableEvents) && $enableFrontendEventSending;
    }

    /**
     * @param $product
     * @return array
     */
    public function redditPixelAddToWishlistPushData($product)
    {
        $result = [
            'track' => 'track',
            'eventName' => \WeltPixel\GA4\Model\Config\Source\RedditPixel\TrackingEvents::EVENT_ADD_TO_WISHLIST,
            'eventData' => [
                'value' => floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', '')),
                'currency' => $this->getCurrencyCode(),
                'conversionId' => $this->getAddToWishlistEventConversionID(),
                'products' => [
                    [
                        'id' => $this->getRedditProductId($product),
                        'name' => addslashes(str_replace('"','&quot;',html_entity_decode($product->getName() ?? ''))),
                        'category' => addslashes(str_replace('"','&quot;',$this->getContentCategory($product->getCategoryIds())))
                    ]
                ]
            ]
        ];

        return $result;
    }

    /**
     * @param $product
     * @param int $qty
     * @return array
     */
    public function redditPixelAddToCartPushData($product, $qty = 1)
    {
        $result = [
            'track' => 'track',
            'eventName' => \WeltPixel\GA4\Model\Config\Source\RedditPixel\TrackingEvents::EVENT_ADD_TO_CART,
            'eventData' => [
                'value' => floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', '')),
                'currency' => $this->getCurrencyCode(),
                'itemCount' => $qty,
                'conversionId' => $this->getAddToCartEventConversionID(),
                'products' => [
                    [
                        'id' => $this->getRedditProductId($product),
                        'name' => addslashes(str_replace('"','&quot;',html_entity_decode($product->getName() ?? ''))),
                        'category' => addslashes(str_replace('"','&quot;',$this->getContentCategory($product->getCategoryIds())))
                    ]
                ]
            ]
        ];

        return $result;
    }

    /**
     * Returns the product id or sku based on the backend settings
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getRedditProductId($product)
    {
        $idOption = $this->_redditPixelOptions['general_tracking']['id_selection'];
        $metaProductId = '';

        switch ($idOption) {
            case 'sku':
                $metaProductId = $product->getData('sku');
                break;
            case 'id':
            default:
                $metaProductId = $product->getId();
                break;
        }

        return $metaProductId;
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

    /**
     * @return string
     */
    public function getEventConversionID()
    {
        $randomString = [];
        for ($i=1; $i<8; $i++) {
            $randomString[] = substr(md5(mt_rand()), 0, 8);
        }

        return implode('', $randomString);
    }

    /**
     * @return string
     */
    public function getAddToWishlistEventConversionID() {
        if (!$this->registry->registry('redditkss_add_to_wishlist_event_uid')) {
            $this->registry->register('redditkss_add_to_wishlist_event_uid', $this->getEventConversionID());
        }

        return $this->registry->registry('redditkss_add_to_wishlist_event_uid');
    }

    /**
     * @return string
     */
    public function getAddToCartEventConversionID() {
        if (!$this->registry->registry('redditss_add_to_cart_event_uid')) {
            $this->registry->register('redditss_add_to_cart_event_uid', $this->getEventConversionID());
        }

        return $this->registry->registry('redditss_add_to_cart_event_uid');
    }

    /**
     * @return string
     */
    public function getSignUpEventConversionID() {
        if (!$this->registry->registry('redditss_signup_event_uid')) {
            $this->registry->register('redditss_signup_event_uid', $this->getEventConversionID());
        }

        return $this->registry->registry('redditss_signup_event_uid');
    }

    /**
     * @return boolean
     */
    public function isServerSideTrackingEnabled()
    {
        if (!$this->_moduleManager->isEnabled('WeltPixel_GA4RedditSS')) {
            return false;
        }
        $isServerSideTrackingEnabled = $this->_redditPixelOptions['serverside_tracking']['enable'];
        if (empty($isServerSideTrackingEnabled)) {
            return false;
        }
        return $isServerSideTrackingEnabled;
    }

    /**
     * @return boolean
     */
    public function enableRedditPixelFrontendEventSending()
    {
        return $this->_redditPixelOptions['serverside_tracking']['enable_frontend_event_sending'] ?? true;
    }

    /**
     * @return array
     */
    public function getRedditServerSideTrackedEvents() {
        $trackedEvents = $this->_redditPixelOptions['serverside_tracking']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldRedditServerSideEventBeTracked($eventName) {
        $availableEvents = $this->getRedditServerSideTrackedEvents();
        return in_array($eventName, $availableEvents);
    }

    /**
     * @return string
     */
    public function getRedditPixelId() {
        return trim($this->_redditPixelOptions['serverside_tracking']['reddit_pixel_id'] ?? '');
    }

    /**
     * @return string
     */
    public function getRedditPixelApiAccessToken() {
        return trim($this->_redditPixelOptions['serverside_tracking']['reddit_api_access_token'] ?? '');
    }

    /**
     * @return boolean
     */
    public function isRedditPixelTestModeEnabled()
    {
        return $this->_redditPixelOptions['serverside_tracking']['enable_test_mode'] ?? false;
    }

    /**
     * @return string
     */
    public function getRedditSSTrackUrl()
    {
        return $this->_getUrl('wpx_reddit/pixel/tracker');
    }

    /**
     * @return mixed
     */
    public function getStoreCurrenUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl(false);
    }
}
