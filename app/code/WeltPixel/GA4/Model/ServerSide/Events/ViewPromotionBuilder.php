<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionBuilderInterface;
use WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionInterface;
use WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\ViewPromotionItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;
use WeltPixel\GA4\Model\Dimension as DimensionModel;

class ViewPromotionBuilder implements ViewPromotionBuilderInterface
{

    /**
     * @var ViewPromotionInterfaceFactory
     */
    protected $viewPromotionFactory;

    /**
     * @var ViewPromotionItemInterfaceFactory
     */
    protected $viewPromotionItemFactory;

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
     * @param ViewPromotionInterfaceFactory $viewPromotionFactory
     * @param ViewPromotionItemInterfaceFactory $viewPromotionItemFactory
     * @param GA4Helper $ga4Helper
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerSession $customerSession
     * @param DimensionModel $dimensionModel
     */
    public function __construct(
        ViewPromotionInterfaceFactory $viewPromotionFactory,
        ViewPromotionItemInterfaceFactory $viewPromotionItemFactory,
        GA4Helper $ga4Helper,
        ProductRepositoryInterface $productRepository,
        CustomerSession $customerSession,
        DimensionModel $dimensionModel
    )
    {
        $this->viewPromotionFactory = $viewPromotionFactory;
        $this->viewPromotionItemFactory = $viewPromotionItemFactory;
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
     * @return null|ViewPromotionInterface
     */
    public function getViewPromotionEvent($promotionId, $promotionName, $creativeName, $creativeSlot, $promoItemIds) {

        /** @var ViewPromotionInterface $viewPromotionEvent */
        $viewPromotionEvent = $this->viewPromotionFactory->create();


        $userProperties = $this->ga4Helper->getUserProperties();
        if ($userProperties) {
            $viewPromotionEvent->setUserProperties($userProperties);
        }
        $pageLocation = $this->ga4Helper->getPageLocation();
        $clientId = $this->ga4Helper->getClientId();
        $sessionIdAndTimeStamp = $this->ga4Helper->getSessionIdAndTimeStamp();
        $userId = $this->customerSession->getCustomerId();
        $currencyCode = $this->ga4Helper->getCurrencyCode();

        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $viewPromotionEvent->setUserId($userId);
        }
        $viewPromotionEvent->setPageLocation($pageLocation);
        $viewPromotionEvent->setClientId($clientId);
        if ($sessionIdAndTimeStamp['session_id']) {
            $viewPromotionEvent->setSessionId($sessionIdAndTimeStamp['session_id']);
        }
        if ($sessionIdAndTimeStamp['timestamp']) {
            $viewPromotionEvent->setTimestamp($sessionIdAndTimeStamp['timestamp']);
        }
        $viewPromotionEvent->setPromotionId($promotionId);
        $viewPromotionEvent->setPromotionName($promotionName);
        $viewPromotionEvent->setCreativeSlot($creativeSlot);
        $viewPromotionEvent->setCreativeName($creativeName);

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

                $viewPromotionItemEvent = $this->viewPromotionItemFactory->create();
                $viewPromotionItemEvent->setParams($productItemOptions);

                $viewPromotionEvent->addItem($viewPromotionItemEvent);
                $index += 1;
            }
        }


        return $viewPromotionEvent;
    }
}
