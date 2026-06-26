<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\PurchaseInterface;
use WeltPixel\GA4\Api\ServerSide\Events\PurchaseInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\PurchaseItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;
use WeltPixel\GA4\Model\Dimension as DimensionModel;

class PurchaseBuilder implements \WeltPixel\GA4\Api\ServerSide\Events\PurchaseBuilderInterface
{
    /**
     * @var PurchaseInterfaceFactory
     */
    protected $purchaseFactory;

    /**
     * @var PurchaseItemInterfaceFactory
     */
    protected $purchaseItemFactory;

    /**
     * @var GA4Helper
     */
    protected $ga4Helper;

    /**
     * @var DimensionModel
     */
    protected $dimensionModel;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param PurchaseInterfaceFactory $purchaseFactory
     * @param PurchaseItemInterfaceFactory $purchaseItemFactory
     * @param GA4Helper $ga4Helper
     * @param DimensionModel $dimensionModel
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        PurchaseInterfaceFactory $purchaseFactory,
        PurchaseItemInterfaceFactory $purchaseItemFactory,
        GA4Helper $ga4Helper,
        DimensionModel $dimensionModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->purchaseFactory = $purchaseFactory;
        $this->purchaseItemFactory = $purchaseItemFactory;
        $this->ga4Helper = $ga4Helper;
        $this->dimensionModel = $dimensionModel;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $order
     * @param boolean
     * @return null|PurchaseInterface
     */
    public function getPurchaseEvent($order, $isAdmin = false)
    {
        /** @var PurchaseInterface $purchaseEvent */
        $purchaseEvent = $this->purchaseFactory->create();

        if (!$order) {
            return $purchaseEvent;
        }

        if ($isAdmin) {
            $this->ga4Helper->reloadConfigOptions($order->getStoreId());
        }

        $userProperties = $this->ga4Helper->getUserProperties();
        if ($userProperties) {
            $purchaseEvent->setUserProperties($userProperties);
        }
        $pageLocation = $this->ga4Helper->getPageLocation(false);
        $clientId = $order->getData('ga_cookie');
        $gaSessionId = $order->getData('ga_session_id');
        $gaTimestamp = $order->getData('ga_timestamp');
        $userId = $order->getCustomerId();

        $currencyCode = $order->getOrderCurrencyCode();

        $purchaseEvent->setStoreId($order->getStoreId());
        $purchaseEvent->setPageLocation($pageLocation);
        $purchaseEvent->setClientId($clientId);
        if ($gaSessionId) {
            $purchaseEvent->setSessionId($gaSessionId);
        }
        if ($gaTimestamp) {
            $purchaseEvent->setTimestamp($gaTimestamp);
        }
        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $purchaseEvent->setUserId($userId);
        }
        $purchaseEvent->setTransactionId($order->getIncrementId());
        $purchaseEvent->setOrderId($order->getId());
        $purchaseEvent->setCoupon((string)$order->getCouponCode());
        $purchaseEvent->setValue(floatval(number_format($this->getOrderTotal($order), 2, '.', '')));
        $purchaseEvent->setShipping(floatval(number_format($order->getShippingAmount(), 2, '.', '')));
        $purchaseEvent->setTax(floatval(number_format($order->getTaxAmount(), 2, '.', '')));
        $purchaseEvent->setCurrency($currencyCode);

        $displayOption = $this->ga4Helper->getParentOrChildIdUsage();
        $priceIncludesTax = $this->ga4Helper->getTaxCalculationPriceIncludesTax();

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
            $productItemOptions['item_id'] = $this->ga4Helper->getGtmProductId($productIdModel);
            if ($isAdmin) {
                $productItemOptions['affiliation'] = $this->ga4Helper->getAffiliationNameByStore($order->getStoreId());
            } else {
                $productItemOptions['affiliation'] = $this->ga4Helper->getAffiliationName();
            }
            $itemOriginalPrice = $item->getOriginalPrice();
            if ($priceIncludesTax) {
                $productItemOptions['price'] = floatval(number_format($item->getPriceInclTax() ?? 0, 2, '.', ''));
                $itemOriginalPrice = $item->getPriceInclTax();
            } else {
                $productItemOptions['price'] = floatval(number_format($item->getPrice() ?? 0, 2, '.', ''));
            }
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
            if ($priceIncludesTax) {
                $finalProductPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $productDiscountedSpecialPrice = $item->getOriginalPrice() - $finalProductPrice;

                if ($productDiscountedSpecialPrice && ($item->getOriginalPrice() > 0)) {
                    $specialPricePercent = $finalProductPrice ? ($finalProductPrice / $item->getOriginalPrice()) * 100 : 0;
                    if ($specialPricePercent) {
                        $productDiscountedSpecialPrice = $itemOriginalPrice *  (100 - $specialPricePercent) / $specialPricePercent;
                        $itemOriginalPrice += $productDiscountedSpecialPrice;
                    }
                }
            } else {
                $productDiscountedSpecialPrice =$itemOriginalPrice - $item->getPrice();
            }
            if ($item->getDiscountAmount() > 0 || ($productDiscountedSpecialPrice > 0) ) {
                $discountValuePerItem = $item->getDiscountAmount() / $item->getQtyOrdered()  + ($productDiscountedSpecialPrice);
                $productItemOptions['discount'] = floatval(number_format($discountValuePerItem, 2, '.', ''));
                $productItemOptions['price'] = floatval(number_format($itemOriginalPrice ? ($itemOriginalPrice - $discountValuePerItem) : 0, 2, '.', ''));
            }

            /**  Set the custom dimensions */
            $customDimensions = $this->dimensionModel->getProductDimensions($product, $this->ga4Helper);
            foreach ($customDimensions as $name => $value) :
                $productItemOptions[$name] = $value;
            endforeach;

            $purchaseItem = $this->purchaseItemFactory->create();
            $purchaseItem->setParams($productItemOptions);

            $purchaseEvent->addItem($purchaseItem);

        }

        return $purchaseEvent;
    }

    /**
     * Retuns the order total (subtotal or grandtotal)
     * @return float
     */
    protected function getOrderTotal($order)
    {
        $orderTotalCalculationOption = $this->ga4Helper->getOrderTotalCalculation();
        switch ($orderTotalCalculationOption) {
            case \WeltPixel\GA4\Model\Config\Source\OrderTotalCalculation::CALCULATE_SUBTOTAL:
                $orderTotal = $order->getSubtotal();
                break;
            case \WeltPixel\GA4\Model\Config\Source\OrderTotalCalculation::CALCULATE_GRANDTOTAL:
            default:
                $orderTotal = $order->getGrandtotal();
                if ($this->ga4Helper->excludeTaxFromTransaction()) {
                    $orderTotal -= $order->getTaxAmount();
                }

                if ($this->ga4Helper->excludeShippingFromTransaction()) {
                    $orderTotal -= $order->getShippingAmount();
                    if ($this->ga4Helper->excludeShippingFromTransactionIncludingTax()) {
                        $orderTotal -= $order->getShippingTaxAmount();
                    }
                }
                break;
        }

        return $orderTotal;
    }

    /**
     * @return array
     */
    public function getMeasurementMissedOrderIds()
    {
        $connection = $this->resourceConnection->getConnection();
        $salesOrderTable = $this->resourceConnection->getTableName('sales_order');
        $salesOrderGridTable = $this->resourceConnection->getTableName('sales_order_grid');
        $weltpixelOrdersPushedTable = $this->resourceConnection->getTableName('weltpixel_ga4_orders_pushed');

        $connection->query(
        // phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
            "UPDATE $salesOrderTable
             SET sent_to_measurement = 1
             WHERE entity_id in (
             SELECT entity_id FROM $salesOrderGridTable
             LEFT JOIN $weltpixelOrdersPushedTable
             ON $salesOrderGridTable.entity_id = $weltpixelOrdersPushedTable.order_id
             WHERE sent_to_measurement = 0
             AND created_at < date_sub(NOW(), INTERVAL 2 MINUTE)
             AND $weltpixelOrdersPushedTable.order_id IS NOT NULL)"
        );

        $select = $connection->select()
            ->from($salesOrderTable, ['entity_id'])
            ->where('sent_to_measurement = ?', 0)
            ->where('created_at < date_sub(NOW(), INTERVAL 2 MINUTE)');

        $orderIds = $connection->fetchCol($select);

        return $orderIds;
    }

}
