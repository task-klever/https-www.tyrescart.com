<?php

namespace WeltPixel\GA4\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetaPixelTracking extends Data
{
    /**
     * @return boolean
     */
    public function isMetaPixelTrackingEnabled() {
        return $this->_metaPixelOptions['general_tracking']['enable'];
    }

    /**
     * @return string
     */
    public function getMetaPixelCodeSnippet() {
        return trim($this->_metaPixelOptions['general_tracking']['code_snippet'] ?? '');
    }


    /**
     * @return array
     */
    public function getMetaPixelTrackedEvents() {
        $trackedEvents = $this->_metaPixelOptions['general_tracking']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldMetaPixelEventBeTracked($eventName) {
        $enableFrontendEventSending = true;
        $serverSideTrackingEnabled = $this->isServerSideTrackingEnabled();

        if ($serverSideTrackingEnabled) {
            $enableFrontendEventSending = $this->enableMetaPixelFrontendEventSending();
        }

        $availableEvents = $this->getMetaPixelTrackedEvents();
        return in_array($eventName, $availableEvents) && $enableFrontendEventSending;
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
     * @param $product
     * @param int $qty
     * @return array
     */
    public function metaPixelAddToCartPushData($product, $qty = 1)
    {
        $result = [
            'track' => 'track',
            'eventName' => 'AddToCart',
            'eventData' => [],
            'additionalParams' => [
                'eventID' => $this->getAddToCartEventUID()
            ]
        ];

        $productId = $this->getMetaProductId($product);
        $productCategoryIds = $product->getCategoryIds();

        $result['eventData']['content_type'] = 'product';
        $result['eventData']['quantity'] = $qty;
        $result['eventData']['currency'] = $this->getCurrencyCode();
        $result['eventData']['content_ids'] = [$productId];
        $result['eventData']['content_name'] = addslashes(str_replace('"','&quot;', html_entity_decode($product->getName() ?? '')));
        $result['eventData']['content_category'] = addslashes(str_replace('"','&quot;',$this->getContentCategory($productCategoryIds)));
        $result['eventData']['value'] = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));

        return $result;
    }

    /**
     * @param $product
     * @return array
     */
    public function metaPixelAddToWishlistPushData($product)
    {
        $result = [
            'track' => 'track',
            'eventName' => 'AddToWishlist',
            'eventData' => [],
            'additionalParams' => [
                'eventID' => $this->getAddToWishlistEventUID()
            ]
        ];

        $productId = $this->getMetaProductId($product);
        $productCategoryIds = $product->getCategoryIds();

        $result['eventData']['content_type'] = 'product';
        $result['eventData']['currency'] = $this->getCurrencyCode();
        $result['eventData']['content_ids'] = [$productId];
        $result['eventData']['content_name'] = addslashes(str_replace('"','&quot;', html_entity_decode($product->getName() ?? '')));
        $result['eventData']['content_category'] = addslashes(str_replace('"','&quot;', $this->getContentCategory($productCategoryIds)));
        $result['eventData']['value'] = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));

        return $result;
    }

    /**
     * Returns the product id or sku based on the backend settings
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getMetaProductId($product)
    {
        $idOption = $this->_metaPixelOptions['general_tracking']['id_selection'];
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
     * @return string
     */
    public function getEventUID()
    {
        $randomString = [];
        for ($i=1; $i<5; $i++) {
            $randomString[] = substr(md5(mt_rand()), 0, 8);
        }

        return implode('-', $randomString);
    }

    /**
     * @return boolean
     */
    public function isServerSideTrackingEnabled()
    {
        if (!$this->_moduleManager->isEnabled('WeltPixel_GA4MetaSS')) {
            return false;
        }
        $isServerSideTrackingEnabled = $this->_metaPixelOptions['serverside_tracking']['enable'];
        if (empty($isServerSideTrackingEnabled)) {
            return false;
        }
        return $isServerSideTrackingEnabled;
    }

    /**
     * @return boolean
     */
    public function enableMetaPixelFrontendEventSending()
    {
        return $this->_metaPixelOptions['serverside_tracking']['enable_frontend_event_sending'] ?? true;
    }

    /**
     * @return array
     */
    public function getMetaServerSideTrackedEvents() {
        $trackedEvents = $this->_metaPixelOptions['serverside_tracking']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldMetaServerSideEventBeTracked($eventName) {
        $availableEvents = $this->getMetaServerSideTrackedEvents();
        return in_array($eventName, $availableEvents);
    }

    /**
     * @return string
     */
    public function getMetaPixelId() {
        return trim($this->_metaPixelOptions['serverside_tracking']['metapixel_id'] ?? '');
    }

    /**
     * @return boolean
     */
    public function isMetaPixelTestModeEnabled()
    {
        return $this->_metaPixelOptions['serverside_tracking']['enable_test_mode'] ?? false;
    }

    /**
     * @return string
     */
    public function getMetaPixelTestEventCode() {
        return trim($this->_metaPixelOptions['serverside_tracking']['test_event_code'] ?? '');
    }

    /**
     * @return string
     */
    public function getMetaPixelConversionApiKey() {
        return trim($this->_metaPixelOptions['serverside_tracking']['conversion_api_access_key'] ?? '');
    }

    /**
     * @return string
     */
    public function getMetaSSTrackUrl()
    {
        return $this->_getUrl('wpx_meta/pixel/tracker');
    }

    /**
     * @return mixed
     */
    public function getStoreCurrenUrl()
    {
        $currentUrl = $this->storeManager->getStore()->getCurrentUrl();
        return $this->removeQueryString($currentUrl);
    }

    /**
     * @param $url
     * @return string
     */
    protected function removeQueryString($url) {
        $parsedUrl = parse_url($url);
        $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        if (isset($parsedUrl['port'])) {
            $cleanUrl .= ':' . $parsedUrl['port'];
        }

        if (isset($parsedUrl['path'])) {
            $cleanUrl .= $parsedUrl['path'];
        }

        return $cleanUrl;
    }

    /**
     * @return string
     */
    public function getAddToWishlistEventUID() {
        if (!$this->registry->registry('metass_add_to_wishlist_event_uid')) {
            $this->registry->register('metass_add_to_wishlist_event_uid', $this->getEventUID());
        }

        return $this->registry->registry('metass_add_to_wishlist_event_uid');
    }

    /**
     * @return string
     */
    public function getAddToCartEventUID() {
        if (!$this->registry->registry('metass_add_to_cart_event_uid')) {
           $this->registry->register('metass_add_to_cart_event_uid', $this->getEventUID());
        }

        return $this->registry->registry('metass_add_to_cart_event_uid');
    }

}
