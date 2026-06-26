<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\AddPaymentInfoInterface;
use WeltPixel\GA4\Api\ServerSide\Events\AddPaymentInfoInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\AddPaymentInfoItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;
use WeltPixel\GA4\Model\Dimension as DimensionModel;

class AddPaymentInfoBuilder implements \WeltPixel\GA4\Api\ServerSide\Events\AddPaymentInfoBuilderInterface
{
    /**
     * @var AddPaymentInfoInterfaceFactory
     */
    protected $addPaymentInfoFactory;

    /**
     * @var AddPaymentInfoItemInterfaceFactory
     */
    protected $addPaymentInfoItemFactory;

    /**
     * @var GA4Helper
     */
    protected $ga4Helper;

    /**
     * @var DimensionModel
     */
    protected $dimensionModel;

    /**
     * @param AddPaymentInfoInterfaceFactory $addPaymentInfoFactory
     * @param AddPaymentInfoItemInterfaceFactory $addPaymentInfoItemFactory
     * @param GA4Helper $ga4Helper
     * @param DimensionModel $dimensionModel
     */
    public function __construct(
        AddPaymentInfoInterfaceFactory $addPaymentInfoFactory,
        AddPaymentInfoItemInterfaceFactory $addPaymentInfoItemFactory,
        GA4Helper $ga4Helper,
        DimensionModel $dimensionModel
    )
    {
        $this->addPaymentInfoFactory = $addPaymentInfoFactory;
        $this->addPaymentInfoItemFactory = $addPaymentInfoItemFactory;
        $this->ga4Helper = $ga4Helper;
        $this->dimensionModel = $dimensionModel;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $paymentType
     * @return null|AddPaymentInfoInterface
     */
    public function getAddPaymentInfoEvent($order, $paymentType)
    {
        /** @var AddPaymentInfoInterface $addPaymentInfoEvent */
        $addPaymentInfoEvent = $this->addPaymentInfoFactory->create();

        if (!$order) {
            return $addPaymentInfoEvent;
        }

        $userProperties = $this->ga4Helper->getUserProperties();
        if ($userProperties) {
            $addPaymentInfoEvent->setUserProperties($userProperties);
        }
        $userId = $order->getCustomerId();
        $pageLocation = $this->ga4Helper->getPageLocation();
        $clientId = $this->ga4Helper->getClientId();
        $sessionIdAndTimeStamp = $this->ga4Helper->getSessionIdAndTimeStamp();

        $currencyCode = $order->getOrderCurrencyCode();

        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $addPaymentInfoEvent->setUserId($userId);
        }
        $addPaymentInfoEvent->setClientId($clientId);
        $addPaymentInfoEvent->setPageLocation($pageLocation);
        if ($sessionIdAndTimeStamp['session_id']) {
            $addPaymentInfoEvent->setSessionId($sessionIdAndTimeStamp['session_id']);
        }
        if ($sessionIdAndTimeStamp['timestamp']) {
            $addPaymentInfoEvent->setTimestamp($sessionIdAndTimeStamp['timestamp']);
        }
        if ($order->getCouponCode()) {
            $addPaymentInfoEvent->setCoupon((string)$order->getCouponCode());
        }
        $addPaymentInfoEvent->setValue(floatval(number_format($order->getGrandTotal(), 2, '.', '')));
        $addPaymentInfoEvent->setCurrency($currencyCode);
        $addPaymentInfoEvent->setPaymentType($paymentType);

        $displayOption = $this->ga4Helper->getParentOrChildIdUsage();

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $productIdModel = $product;
            $configurableVariantChildSku = null;
            if ($displayOption == \WeltPixel\GA4\Model\Config\Source\ParentVsChild::CHILD) {
                if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $children = $item->getChildrenItems();
                    foreach ($children as $child) {
                        $productIdModel = $child->getProduct();
                        $configurableVariantChildSku = $child->getData('sku');
                    }
                }
            }

            $productItemOptions = [];
            $productItemOptions['item_name'] = $this->ga4Helper->getProductName($productIdModel);
            $productItemOptions['item_id'] = $this->ga4Helper->getGtmProductId($productIdModel); //$this->helper->getGtmOrderItemId($item);
            $productItemOptions['affiliation'] = $this->ga4Helper->getAffiliationName();
            $productItemOptions['price'] = floatval(number_format($item->getPrice(), 2, '.', ''));
            if ($this->ga4Helper->isBrandEnabled()) {
                $productItemOptions['item_brand'] = $this->ga4Helper->getGtmBrand($product);
            }
            if ($this->ga4Helper->isVariantEnabled()) {
                $itemVariant = $this->ga4Helper->getItemVariant();
                if ((\WeltPixel\GA4\Model\Config\Source\ItemVariant::CHILD_SKU == $itemVariant) && ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)) {
                    if (!$configurableVariantChildSku) {
                        $children = $item->getChildrenItems();
                        foreach ($children as $child) {
                            $configurableVariantChildSku = $child->getData('sku');
                        }
                    }
                    if ($configurableVariantChildSku) {
                        $productItemOptions['item_variant'] = $configurableVariantChildSku;
                    }
                } else {
                    $productOptions = $item->getData('product_options');
                    $productType = $item->getData('product_type');
                    $variant = $this->ga4Helper->checkVariantForProductOptions($productOptions, $productType);
                    if ($variant) {
                        $productItemOptions['item_variant'] = $variant;
                    }
                }
            }
            $productCategoryIds = $product->getCategoryIds();
            $categoryName = $this->ga4Helper->getGtmCategoryFromCategoryIds($productCategoryIds);
            $ga4Categories = $this->ga4Helper->getGA4CategoriesFromCategoryIds($productCategoryIds);
            $productItemOptions = array_merge($productItemOptions, $ga4Categories);
            $productItemOptions['item_list_name'] = $categoryName;
            $productItemOptions['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : '';
            $productItemOptions['quantity'] = (double)$item->getQtyOrdered();

            /**  Set the custom dimensions */
            $customDimensions = $this->dimensionModel->getProductDimensions($product, $this->ga4Helper);
            foreach ($customDimensions as $name => $value) :
                $productItemOptions[$name] = $value;
            endforeach;

            $addPaymentInfoItem = $this->addPaymentInfoItemFactory->create();
            $addPaymentInfoItem->setParams($productItemOptions);

            $addPaymentInfoEvent->addItem($addPaymentInfoItem);
        }

        return $addPaymentInfoEvent;
    }

}
