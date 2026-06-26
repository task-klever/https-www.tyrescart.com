<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionBuilderInterface;
use WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionInterface;
use WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\SelectPromotionItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;
use WeltPixel\GA4\Model\Dimension as DimensionModel;

class SelectPromotionBuilder implements SelectPromotionBuilderInterface
{

    /**
     * @var SelectPromotionInterfaceFactory
     */
    protected $selectPromotionFactory;

    /**
     * @var SelectPromotionItemInterfaceFactory
     */
    protected $selectPromotionItemFactory;

    /**
     * @var GA4Helper
     */
    protected $ga4Helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var DimensionModel
     */
    protected $dimensionModel;

    /**
     * @param SelectPromotionInterfaceFactory $selectPromotionFactory
     * @param SelectPromotionItemInterfaceFactory $selectPromotionItemFactory
     * @param GA4Helper $ga4Helper
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerSession $customerSession
     * @param DimensionModel $dimensionModel
     */
    public function __construct(
        SelectPromotionInterfaceFactory $selectPromotionFactory,
        SelectPromotionItemInterfaceFactory $selectPromotionItemFactory,
        GA4Helper $ga4Helper,
        ProductRepositoryInterface $productRepository,
        CustomerSession $customerSession,
        DimensionModel $dimensionModel
    )
    {
        $this->selectPromotionFactory = $selectPromotionFactory;
        $this->selectPromotionItemFactory = $selectPromotionItemFactory;
        $this->ga4Helper = $ga4Helper;
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->dimensionModel = $dimensionModel;
    }
    /**
     * @param $promotionId
     * @param $promotionName
     * @param $creativeName
     * @param $creativeSlot
     * @param $promoItemIds
     * @return null|SelectPromotionInterface
     */
    public function getSelectPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoItemIds) {

        /** @var SelectPromotionInterface $selectPromotionEvent */
        $selectPromotionEvent = $this->selectPromotionFactory->create();

        $userProperties = $this->ga4Helper->getUserProperties();
        if ($userProperties) {
            $selectPromotionEvent->setUserProperties($userProperties);
        }
        $pageLocation = $this->ga4Helper->getPageLocation();
        $clientId = $this->ga4Helper->getClientId();
        $sessionIdAndTimeStamp = $this->ga4Helper->getSessionIdAndTimeStamp();
        $userId = $this->customerSession->getCustomerId();
        $currencyCode = $this->ga4Helper->getCurrencyCode();

        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $selectPromotionEvent->setUserId($userId);
        }
        $selectPromotionEvent->setPageLocation($pageLocation);
        $selectPromotionEvent->setClientId($clientId);
        if ($sessionIdAndTimeStamp['session_id']) {
            $selectPromotionEvent->setSessionId($sessionIdAndTimeStamp['session_id']);
        }
        if ($sessionIdAndTimeStamp['timestamp']) {
            $selectPromotionEvent->setTimestamp($sessionIdAndTimeStamp['timestamp']);
        }
        $selectPromotionEvent->setPromotionId($promotionId);
        $selectPromotionEvent->setPromotionName($promotionName);
        $selectPromotionEvent->setCreativeSlot($creativeSlot);
        $selectPromotionEvent->setCreativeName($creativeName);

        if ($promoItemIds) {
            $index = 1;
            foreach ($promoItemIds as $productId) {
                try {
                    $product = $this->productRepository->getById($productId);
                } catch (\Exception $ex) {
                    continue;
                }

                $productItemOptions = [];
                $productPrice = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));
                $productItemOptions['item_name'] =  $this->ga4Helper->getProductName($product);
                $productItemOptions['item_id'] = $this->ga4Helper->getGtmProductId($product);
                $productItemOptions['affiliation'] = $this->ga4Helper->getAffiliationName();
                $productItemOptions['price'] = $productPrice;
                if ($this->ga4Helper->isBrandEnabled()) {
                    $productItemOptions['item_brand'] = $this->ga4Helper->getGtmBrand($product);
                }
                $productCategoryIds = $product->getCategoryIds();
                $ga4Categories = $this->ga4Helper->getGA4CategoriesFromCategoryIds($productCategoryIds);
                $productItemOptions = array_merge($productItemOptions, $ga4Categories);
                $productItemOptions['quantity'] = 1;
                $productItemOptions['index'] = $index;
                $productItemOptions['currency'] = $currencyCode;
                $productItemOptions['item_list_name'] = __('Promotion List From') . ' ' . $promotionName;
                $productItemOptions['item_list_id'] = 'promotion_list';

                /**  Set the custom dimensions */
                $customDimensions = $this->dimensionModel->getProductDimensions($product, $this->ga4Helper);
                foreach ($customDimensions as $name => $value) :
                    $productItemOptions[$name] = $value;
                endforeach;

                $selectPromotionItemEvent = $this->selectPromotionItemFactory->create();
                $selectPromotionItemEvent->setParams($productItemOptions);

                $selectPromotionEvent->addItem($selectPromotionItemEvent);
                $index += 1;
            }
        }


        return $selectPromotionEvent;
    }
}
