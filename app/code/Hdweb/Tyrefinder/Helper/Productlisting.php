<?php

namespace Hdweb\Tyrefinder\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Productlisting extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_storeManager;
    protected $_scopeConfig;
    protected $_pricing;
    const SETFOUR = 4;
    const SETTWO = 2;
    const SETONE = 1;
    protected $ruleFactory;
    protected $datetime;
    protected $brandModel;
    protected $timezoneInterface;
    protected $productModel;
    protected $taxCalculationRate;
    protected $moduleManager;
    protected $request;
    protected $productFactory;
    protected $stockResolver;
    protected $salableQtyService;
    protected $stockRegistry;
    protected $salesChannelInterface;
    protected $_cachedRules = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \MGS\Brand\Model\Brand $brandModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Tax\Model\Calculation\Rate $taxCalculationRate,
        ModuleManager $moduleManager,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        StockResolverInterface $stockResolver,
        GetProductSalableQtyInterface $salableQtyService,
        StockRegistryInterface $stockRegistry,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannelInterface

    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig  = $scopeConfig;
        $this->_pricing      = $pricing;
        $this->ruleFactory = $ruleFactory;
        $this->datetime = $datetime;
        $this->brandModel = $brandModel;
        $this->timezoneInterface = $timezoneInterface;
        $this->productModel = $productModel;
        $this->taxCalculationRate = $taxCalculationRate;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->stockResolver = $stockResolver;
        $this->salableQtyService = $salableQtyService;
        $this->stockRegistry = $stockRegistry;
        $this->salesChannelInterface = $salesChannelInterface;
        parent::__construct($context);
    }

    public function getTyreSize($_product, $long = null)
    {
        $width       = $this->getAttributeValue($_product, 'width');
        $height      = $this->getAttributeValue($_product, 'height');
        $rim         = $this->getAttributeValue($_product, 'rim');
        $load_index  = $_product->getLoadIndex();
        $tyresize    = "";
        if (isset($width) && !empty($width)) {
            if ($height == '' || $height == 'None') {
                $tyresize = $width . " R" .  $rim . " " . $load_index; // . $speed_index;
            } else {
                $tyresize = $width . '/' . $height . " R" .  $rim . " " . $load_index; // . $speed_index;                
            }
        }

        return $tyresize;
    }

    public function getAttributeValue($_product, $_attribute)
    {
        $_attributeId = $_product->getData($_attribute);
        $attr         = $_product->getResource()->getAttribute($_attribute);
        if ($attr->usesSource()) {
            $attributeValue = $attr->getSource()->getOptionText($_attributeId);
        }
        return $attributeValue;
    }

    public function getSet1price($_product)
    {
        $taxRate = $this->getTaxRate();
        $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
        $set1price   = $final_price * self::SETONE;
        /* if($this->isTaxPriceDisplayIncludingTax() == 2){
            $set1price   = $set1price + ($set1price * ($taxRate/100));
        } */
        //$set1price   = number_format($set1price, 2);
        //$set1price   = number_format(round($set1price), 2, '.', '');
        $set1price   = number_format($set1price, 2, '.', '');
        // $set1price   = $this->_pricing->currency($set1price, true, false);
        return $set1price;
    }

    public function getSet2price($_product)
    {
        $taxRate = $this->getTaxRate();
        $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
        $set2price   = $final_price * self::SETTWO;
        /* if($this->isTaxPriceDisplayIncludingTax() == 2){
            $set2price   = $set2price + ($set2price * ($taxRate/100));
        } */
        //$set2price   = number_format(round($set2price), 2, '.', '');
        $set2price   = number_format($set2price, 2, '.', '');
        //$set2price   = $this->_pricing->currency($set2price, true, false);
        return $set2price;
    }

    public function getSet4price($_product)
    {
        $taxRate = $this->getTaxRate();
        $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
        $set4price   = $final_price * self::SETFOUR;
        /* if($this->isTaxPriceDisplayIncludingTax() == 2){
            $set4price   = $set4price + ($set4price * ($taxRate/100));
        } */


        //$rulesId=$this->isAnyRuleExist($_product->getId());
        //if ($this->moduleManager->isOutputEnabled('Hdweb_Specialoffers')) {
            $productOfferRule = $this->isAnyRuleExist($_product);
            if (!empty($productOfferRule)) {
                if ($productOfferRule['discount_qty_step'] ==  4 && $productOfferRule['discount_type'] == 'by_percent') {
                    $discountPercentage = $productOfferRule['discount_amount'];
                    $set4price   = $set4price - ($set4price * ($discountPercentage / 100));
                }
                if ($productOfferRule['discount_qty_step'] ==  0 && $productOfferRule['discount_type'] == 'by_fixed') {
                    $discountAmount = $productOfferRule['discount_amount'];
                    $set4price   = $set4price - $discountAmount;
                }
            }
        //}
        //$set4price   = number_format(round($set4price), 2, '.', '');
        $set4price   = number_format($set4price, 2, '.', ',');
        //$set4price   = $this->_pricing->currency($set4price, true, false);

        return $set4price;
    }

    public function getVehcileImageUrl($_product, $vehicle_model)
    {
        $vehicle_model_value = $this->getAttributeValue($_product, $vehicle_model);
        $vehicle_model_value = strtolower($vehicle_model_value) . '-icon.svg';
        $imagepath           = 'vehicle_image/' . $vehicle_model_value;
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $imagepath;
    }

    public function getBrandDetails($manufacturerId)
    {
        $brand = $this->brandModel;
        $brands = $brand->getCollection()->addFieldToFilter('option_id', ['eq' => $manufacturerId]);
        $brands->getFirstItem();
        $brandArray = array();
        foreach ($brands as $brandData) {
            $brandArray = $brandData;
        }
        return $brandArray;
    }

    public function getBrandImageUrl($brand)
    {
        if (!empty($brand)) {
            if ($brand->getImage()) {
                $image = $brand->getImage();
                return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $image;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isAnyRuleExist($product)
    {
        $currentStore = $this->_storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediapathofferimage = $mediaUrl . "salesrule/offerimage/";
        $storeCode = $this->_storeManager->getStore()->getCode();
        if ($storeCode == 'ar') {
            $mediapathofferimage = $mediaUrl . "salesrule/offerimage/" . $storeCode . '/';
        }
        if ($this->_cachedRules === null) {
            $currentDate = $this->datetime->date();
            $currentDate = $this->timezoneInterface->date($currentDate)->format('Y-m-d');
            $excludeRuleIds = [12];
            $this->_cachedRules = $this->ruleFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('coupon_type', 1)
                ->addFieldToFilter('from_date', ['lteq' => $currentDate])
                ->addFieldToFilter('to_date', ['gteq' => $currentDate])
                ->addFieldToFilter('rule_id', ['nin' => $excludeRuleIds]);
            $this->_cachedRules->getSelect()->order('sort_order DESC');
        }
        $_rules = $this->_cachedRules;

        $item = $this->productModel;
        $taxRate = $this->getTaxRate();

        $ruleArray = array();
        foreach ($_rules as $rule) {
            $item->setProduct($product);
            $validate = $rule->getActions()->validate($item);
            if ($validate) {
                $ruleArray['color'] = $rule->getColorText();
                $ruleArray['discount_type'] = $rule->getSimpleAction();
                $ruleArray['discount_amount'] = $rule->getDiscountAmount();
                $ruleArray['max_qty_discount_applied_to'] = $rule->getDiscountQty();
                $ruleArray['discount_qty_step'] = $rule->getDiscountStep();


                if ($rule->getDiscountStep() > 3 && $rule->getDiscountAmount() > 0) {
                    $discount_step_qty = $rule->getDiscountStep();
                    $module_qty = 4 % $discount_step_qty;
                    $discounted_qty = 4 - $module_qty;
                    $discount_amount_qty = $rule->getDiscountAmount();
                    //$set1price = $this->getSet1price($product);
                    $set1price = $product->getFinalPrice();
                    //$set1price   = $set1price + ($set1price * ($taxRate/100));
                    $set1priceNumeric = str_replace(',', '', $set1price);
                    $pricefor_discount_item = ($discounted_qty * $set1priceNumeric  * $discount_amount_qty) / 100; //get percenatge
                    $offer_price_with_deducted_ammount = ($discounted_qty * $set1priceNumeric) - $pricefor_discount_item;
                    $pricefor_without_discount_item = $set1priceNumeric * $module_qty;
                    $offer_price = $offer_price_with_deducted_ammount + $pricefor_without_discount_item;

                    //$imageColor[7]= round($offer_price);
                    $ruleArray['offer_price'] = $offer_price;
                } else {
                    $ruleArray['offer_price'] = '';
                }
                $ruleArray['rule_id'] = $rule->getRuleId();
                $ruleArray['rule_name'] = $rule->getName();
            }
        }
        return $ruleArray;
    }
    public function getSet1priceWithoutCurrency($_product)
    {
        $final_price = $_product->getPriceInfo()->getPrice('final_price')->getValue();
        $set1price   = $final_price * self::SETONE;
        $set1price   = $set1price + ($set1price * 0.05);
        //$set1price   = $this->_pricing->currency($set1price, true, false);
        return round($set1price);
    }

    public function getVatIncPrice($price)
    {
        $set1price   = $price + ($price * 0.05);
        $set1price   = number_format(round($set1price), 2);
        // $set1price   = $this->_pricing->currency($set1price, true, false);
        return $set1price;
    }

    public function getTaxRate()
    {
        $taxRateInfo = $this->taxCalculationRate->loadByCode('UAE VAT');
        $rate = $taxRateInfo->getRate();
        return $rate;
    }

    public function isTaxPriceDisplayIncludingTax()
    {
        $path = 'tax/display/type'; // Configuration path for Sales -> Tax -> Price Display Setting
        $storeScope = ScopeInterface::SCOPE_STORE; // You can change the scope according to your needs

        $priceDisplaySetting = $this->_scopeConfig->getValue($path, $storeScope);

        return $priceDisplaySetting;
    }

    public function isBundle()
    {
        $width_rear  = $this->request->getParam('width_rear');
        $height_rear = $this->request->getParam('height_rear');
        $rim_rear    = $this->request->getParam('rim_rear');
        $isBundle    = 0;
        if (isset($width_rear) && isset($height_rear) && isset($rim_rear) && !empty($width_rear) && !empty($height_rear) && !empty($rim_rear)) {
            $isBundle = 1;
        }

        return $isBundle;
    }

    public function getFrontAndRearSizeFromCurrentUrl()
    {
        $width = $this->request->getParam('width');
        $height = $this->request->getParam('height');
        $rim = $this->request->getParam('rim');
        $widthRear = $this->request->getParam('width_rear');
        $heightRear = $this->request->getParam('height_rear');
        $rimRear = $this->request->getParam('rim_rear');

        if (!$width || !$height || !$rim || !$widthRear || !$heightRear || !$rimRear) {
            return null;
        }

        $frontSize = $width . '/' . $height . ' R' . $rim;
        $rearSize = $widthRear . '/' . $heightRear . ' R' . $rimRear;

        $frontSize = str_replace('.', '', $frontSize);
        $rearSize = str_replace('.', '', $rearSize);

        return [
            'front_size' => $frontSize,
            'rear_size' => $rearSize
        ];
    }

    public function getFrontSizeFromUrl()
    {
        $width = $this->request->getParam('width');
        $height = $this->request->getParam('height');
        $rim = $this->request->getParam('rim');

        if (!$width || !$height || !$rim) {
            return null;
        }

        $frontSize = $width . '/' . $height . ' R' . $rim;

        return $frontSize;
    }

    /* Get Label by option id */
    public function getOptionLabelByValue($attributeCode, $optionId)
    {
        $product = $this->productFactory->create();
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode);
        $optionText = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionText = $isAttributeExist->getSource()->getOptionText($optionId);
        }
        return $optionText;
    }

    /* Get Option id by Option Label */
    public function getOptionIdByLabel($attributeCode, $optionLabel)
    {
        $product = $this->productFactory->create();
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }

    public function getBrandUrl($brand)
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl();
        if ($brand->getBrandCategory() == 'Tyres') {
            return $baseUrl . 'brand/' . $brand->getUrlKey();
        } elseif ($brand->getBrandCategory() == 'Battery') {

            return $baseUrl . 'car-batteries/' . $brand->getUrlKey();
        } elseif ($brand->getBrandCategory() == 'Lubricants') {
            return $baseUrl . 'lubricant/' . $brand->getUrlKey();
        } elseif ($brand->getBrandCategory() == 'Accessories') {
            return $baseUrl . 'car-accessories/' . $brand->getUrlKey();
        } else {
            return "#";
        }
    }

    public function getNotCreatedBrandUrl($partsCategory, $selectedBrandInProduct)
    {
        if($partsCategory != '' && $selectedBrandInProduct != ''){
            $baseUrl = $this->_urlBuilder->getBaseUrl();
            $brandUrlKey = strtolower(str_replace(' ', '-', $selectedBrandInProduct));
            if ($partsCategory == 'Tyres') {
                return $baseUrl . 'tyres/brand/' . $brandUrlKey;
            } elseif ($brandUrlKey == 'Battery') {
                return $baseUrl . 'car-batteries/' . $brandUrlKey;
            } elseif ($brandUrlKey == 'Lubricants') {
                return $baseUrl . 'lubricant/' . $brandUrlKey;
            } elseif ($brandUrlKey == 'Accessories') {
                return $baseUrl . 'car-accessories/' . $brandUrlKey;
            } else {
                return "#";
            }
        }else{
            return "#";
        }
    }

    /**
     * Get available quantity for product by SKU
     *
     * @param string $sku
     * @return float
     */
    public function getProductAvailableQty($sku)
    {
        try {
            $websiteCode = $this->_storeManager->getStore()->getWebsite()->getCode();
 
            try {
                $stock = $this->stockResolver->execute($this->salesChannelInterface::TYPE_WEBSITE, $websiteCode);
                $stockId = $stock->getStockId();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                //$stockId = 1; // Fallback to default stock
                return 0;
            }
            $qty = $this->salableQtyService->execute($sku, $stockId);
            return $qty;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
