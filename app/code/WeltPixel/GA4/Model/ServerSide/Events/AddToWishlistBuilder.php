<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use Magento\Customer\Model\Session as CustomerSession;
use WeltPixel\GA4\Api\ServerSide\Events\AddToWishlistInterface;
use WeltPixel\GA4\Api\ServerSide\Events\AddToWishlistInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\AddToWishlistItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;
use WeltPixel\GA4\Model\Dimension as DimensionModel;

class AddToWishlistBuilder implements \WeltPixel\GA4\Api\ServerSide\Events\AddToWishlistBuilderInterface
{
    /**
     * @var AddToWishlistInterfaceFactory
     */
    protected $addToWishlistFactory;

    /**
     * @var AddToWishlistItemInterfaceFactory
     */
    protected $addToWishlistItemFactory;

    /**
     * @var GA4Helper
     */
    protected $ga4Helper;

    /**
     * @var DimensionModel
     */
    protected $dimensionModel;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @param AddToWishlistInterfaceFactory $addToWishlistFactory
     * @param AddToWishlistItemInterfaceFactory $addToWishlistItemFactory
     * @param GA4Helper $ga4Helper
     * @param DimensionModel $dimensionModel
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     */
    public function __construct(
        AddToWishlistInterfaceFactory $addToWishlistFactory,
        AddToWishlistItemInterfaceFactory $addToWishlistItemFactory,
        GA4Helper $ga4Helper,
        DimensionModel $dimensionModel,
        CustomerSession $customerSession,
        \Magento\Framework\DataObject\Factory $objectFactory
    )
    {
        $this->addToWishlistFactory = $addToWishlistFactory;
        $this->addToWishlistItemFactory = $addToWishlistItemFactory;
        $this->ga4Helper = $ga4Helper;
        $this->dimensionModel = $dimensionModel;
        $this->customerSession = $customerSession;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $buyRequest
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return null|AddToWishlistInterface
     */
    public function getAddToWishlistEvent($product, $buyRequest, $wishlistItem)
    {
        /** @var AddToWishlistInterface $addToWishlistEvent */
        $addToWishlistEvent = $this->addToWishlistFactory->create();

        if (!$product) {
            return $addToWishlistEvent;
        }


        $displayOption = $this->ga4Helper->getParentOrChildIdUsage();
        $productId = $this->ga4Helper->getGtmProductId($product);
        if ($buyRequest instanceof \Magento\Framework\DataObject) {
            $buyRequest = $buyRequest->getData();
        }
        $itemName =  $this->ga4Helper->getProductName($product);
        $configurableVariantChildSku = null;
        if ( ($displayOption == \WeltPixel\GA4\Model\Config\Source\ParentVsChild::CHILD) && ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)) {
            $canditatesRequest = $this->objectFactory->create($buyRequest);
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($canditatesRequest, $product);

            if (is_array($cartCandidates) || is_object($cartCandidates)) {
                foreach ($cartCandidates as $candidate) {
                    if ($candidate->getParentProductId()) {
                        $productId = $this->ga4Helper->getGtmProductId($candidate);
                        $itemName =  $this->ga4Helper->getProductName($candidate);
                        $configurableVariantChildSku = $candidate->getData('sku');
                    }
                }
            }
        }

        $userProperties = $this->ga4Helper->getUserProperties();
        if ($userProperties) {
            $addToWishlistEvent->setUserProperties($userProperties);
        }
        $pageLocation = $this->ga4Helper->getPageLocation();
        $clientId = $this->ga4Helper->getClientId();
        $sessionIdAndTimeStamp = $this->ga4Helper->getSessionIdAndTimeStamp();
        $userId = $this->customerSession->getCustomerId();
        $currencyCode = $this->ga4Helper->getCurrencyCode();
        $productPrice = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));

        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $addToWishlistEvent->setUserId($userId);
        }
        $addToWishlistEvent->setPageLocation($pageLocation);
        $addToWishlistEvent->setClientId($clientId);
        if ($sessionIdAndTimeStamp['session_id']) {
            $addToWishlistEvent->setSessionId($sessionIdAndTimeStamp['session_id']);
        }
        if ($sessionIdAndTimeStamp['timestamp']) {
            $addToWishlistEvent->setTimestamp($sessionIdAndTimeStamp['timestamp']);
        }
        $addToWishlistEvent->setCurrency($currencyCode);
        $addToWishlistEvent->setValue($productPrice);

        $addToWishlistItemOptions = [];
        $addToWishlistItemOptions['item_name'] = $itemName;
        $addToWishlistItemOptions['item_id'] = $productId;
        $addToWishlistItemOptions['affiliation'] = $this->ga4Helper->getAffiliationName();
        $addToWishlistItemOptions['index'] = 0;
        $addToWishlistItemOptions['price'] = $productPrice;
        if ($this->ga4Helper->isBrandEnabled()) {
            $addToWishlistItemOptions['item_brand'] = $this->ga4Helper->getGtmBrand($product);
        }

        $productCategoryIds = $product->getCategoryIds();
        $categoryName = $this->ga4Helper->getGtmCategoryFromCategoryIds($product->getCategoryIds());
        $ga4Categories = $this->ga4Helper->getGA4CategoriesFromCategoryIds($productCategoryIds);
        $addToWishlistItemOptions = array_merge($addToWishlistItemOptions, $ga4Categories);
        $addToWishlistItemOptions['item_list_name'] = $categoryName;
        $addToWishlistItemOptions['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : '';
        $addToWishlistItemOptions['quantity'] = 1;

        /**  Set the custom dimensions */
        $customDimensions = $this->dimensionModel->getProductDimensions($product, $this->ga4Helper);
        foreach ($customDimensions as $name => $value) :
            $addToWishlistItemOptions[$name] = $value;
        endforeach;

        if ($this->ga4Helper->isVariantEnabled()) {
            $itemVariant = $this->ga4Helper->getItemVariant();
            if ((\WeltPixel\GA4\Model\Config\Source\ItemVariant::CHILD_SKU == $itemVariant) && ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)) {
                if (!$configurableVariantChildSku) {
                    $canditatesRequest = $this->objectFactory->create($buyRequest);
                    $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($canditatesRequest, $product);
                    if (is_array($cartCandidates) || is_object($cartCandidates)) {
                        foreach ($cartCandidates as $candidate) {
                            if ($candidate->getParentProductId()) {
                                $configurableVariantChildSku = $candidate->getData('sku');
                            }
                        }
                    }
                }
                if ($configurableVariantChildSku) {
                    $addToWishlistItemOptions['item_variant'] = $configurableVariantChildSku;
                }
            } else {
                $variant = $this->ga4Helper->checkVariantForProduct($product, $buyRequest, $wishlistItem);
                if ($variant) {
                    $addToWishlistItemOptions['item_variant'] = $variant;
                }
            }
        }

        $addToWishlistItem = $this->addToWishlistItemFactory->create();
        $addToWishlistItem->setParams($addToWishlistItemOptions);

        $addToWishlistEvent->addItem($addToWishlistItem);

        return $addToWishlistEvent;
    }

}
