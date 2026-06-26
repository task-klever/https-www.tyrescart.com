<?php

namespace MGS\Brand\Block\Brand;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_catalogLayer;
    protected $_brandHelper;
    protected $minimumPrice;
    protected $maximumPrice;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \MGS\Brand\Helper\Data $brandHelper,
        array $data = []
    )
    {
        $this->_brandHelper = $brandHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _addBreadcrumbs()
{
    $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
    if (!$breadcrumbs) {
        return; // prevent errors if breadcrumbs block not found
    }

    $brand = $this->getCurrentBrand();
    if (!$brand) {
        return;
    }

    $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
    $brandRoute = $this->_brandHelper->getConfig('general_settings/route') ?: 'shop-by-brand';
    $pageTitle = $this->_brandHelper->getConfig('list_page_settings/title') ?: __('Shop By Brand');

    // Home
    $breadcrumbs->addCrumb(
        'home',
        [
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link'  => $baseUrl
        ]
    );

    // Shop By Brand
    $breadcrumbs->addCrumb(
        'brand_list',
        [
            'label' => $pageTitle,
            'title' => $pageTitle,
            'link'  => $baseUrl . $brandRoute
        ]
    );

    // Current Brand
    $breadcrumbs->addCrumb(
        'brand_view',
        [
            'label' => $brand->getName(),
            'title' => $brand->getName(),
            'link'  => ''
        ]
    );
}


    public function getCurrentBrand()
    {
        $brand = $this->_coreRegistry->registry('current_brand');
        if ($brand) {
            $this->setData('current_brand', $brand);
        }
        return $brand;
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_brandHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

   protected function _prepareLayout()
{
    $brand = $this->getCurrentBrand();
    if ($brand) {
        $this->_addBreadcrumbs(); // add breadcrumbs

        // Set page title and meta
        //$this->pageConfig->getTitle()->set($brand->getName());

        $this->pageConfig->getTitle()->set($brand->getMetaKeywords());

        if ($brand->getMetaKeywords()) {
            $this->pageConfig->setKeywords($brand->getMetaKeywords());
        }

        if ($brand->getMetaDescription()) {
            $this->pageConfig->setDescription($brand->getMetaDescription());
        }
    }

    return parent::_prepareLayout();
}

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    public function getPatternbyBrand()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $currentStoreId = $storeManager->getStore()->getStoreId();
        
        //$brand = $this->getBrand();
        $brand = $this->getCurrentBrand();
        $brandName = $brand->getName();
        $brandId = $brand->getBrandId();
        //$brandId = $brand->getOptionId();

        $brandPatternObj = $objectManager->get('MGS\Brand\Model\Patternmanagement');
        $brandPatternCollection = $brandPatternObj->getCollection()
                                //->addFieldToFilter('brand_id', $brandId)
                                 ->addFieldToFilter('brand', $brandName)
                                ->addFieldToFilter('status', 1)
                                /*->addFieldToFilter('store_id', $currentStoreId) */;
        return $brandPatternCollection;
        $patternList = array();
        //if(count($brandPatternCollection->getData() >0 )){
        if($brandPatternCollection->count() > 0 ){
            foreach($brandPatternCollection as $brandPatternData){
                $patternList[] = $brandPatternData;
            }
        }
        
        return $patternList;
    }

    public function getPatternProducts($brandId, $patternId)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$collection = $productFactory->create();
		$collection->addAttributeToSelect('entity_id');
		$collection->addAttributeToSelect('rim');
		$collection->addAttributeToSelect('rim_value');
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$collection->addAttributeToFilter('brand', $brandId);
		$collection->addAttributeToFilter('pattern', $patternId);
		$minPrice = null;
		$maxPrice = null;
		foreach ($collection->getItems() as $product) {
			$productLoaded = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
			$price = $productLoaded->getPriceInfo()->getPrice('final_price')->getMinimalPrice();
			if ($minPrice === null || $price < $minPrice) {
				$minPrice = $price;
			}

			if ($maxPrice === null || $price > $maxPrice) {
				$maxPrice = $price;
			}
		}
		$this->minimumPrice = $minPrice;
        $this->maximumPrice = $maxPrice;
		
		return $collection;
	}

    public function getMinimumPrice()
    {
        return $this->minimumPrice;
    }

    public function getMaximumPrice()
    {
        return $this->maximumPrice;
    }
}