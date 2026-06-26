<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\CategoryFactory;
use MGS\Brand\Model\BrandFactory;
use Magento\Store\Model\StoreManagerInterface;

class GetEvBrands extends Action
{
    const CARTYRE_CATEGORY_ID = 'hdweb/general/car_tyre_category_id';

    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $productFactory;
    protected $scopeConfig;
    protected $categoryFactory;
    protected $brandFactory;
    protected $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory,
        BrandFactory $brandFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        $this->brandFactory = $brandFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'document') {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setHttpResponseCode(301);
            return $resultRedirect->setPath('/');
        }

        // Get all brand option IDs that have EV products
        $carTyreCategoryId = $this->scopeConfig->getValue(self::CARTYRE_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        $category = $this->categoryFactory->create()->load($carTyreCategoryId);

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('brand')
            ->addAttributeToFilter('ev', ['notnull' => true])
            ->addAttributeToFilter('ev', ['neq' => ''])
            ->addCategoryFilter($category);
        $collection->getSelect()->group('brand');

        $evBrandOptionIds = [];
        foreach ($collection as $product) {
            $brandVal = $product->getData('brand');
            if (!empty($brandVal)) {
                $evBrandOptionIds[] = $brandVal;
            }
        }

        // Get brand details from MGS Brand
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $brandCollection = $this->brandFactory->create()->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('brand_category', 'Tyres')
            ->addFieldToFilter('option_id', ['in' => $evBrandOptionIds])
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('sort_order', 'ASC');

        $selectHtml = '';
        foreach ($brandCollection as $brand) {
            $name = trim($brand->getName());
            if (!$name) {
                continue;
            }

            $urlKey = $brand->getUrlKey();
            if (!$urlKey) {
                $urlKey = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)));
                $urlKey = trim($urlKey, '-');
            }

            $imageUrl = $brand->getImage() ? $mediaUrl . $brand->getImage() : '';
            $optionId = $brand->getOptionId();
            $escapedName = htmlspecialchars($name, ENT_QUOTES);

            $placeholderImg = '<svg class="w-10 h-10 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>';
            $imageHtml = $imageUrl
                ? '<div class="flex items-center justify-center mx-auto min-h-[65px]"><img src="' . $imageUrl . '" class="max-w-[60px] object-contain" alt="' . $escapedName . '" /></div>'
                : '<div class="flex items-center justify-center mx-auto min-h-[65px]">' . $placeholderImg . '</div>';

            $selectHtml .= '<li class="search" data-name="' . strtolower($escapedName) . '">
                <div onclick="selectEvBrand(\'' . $optionId . '\', \'' . addslashes($name) . '\', \'' . addslashes($urlKey) . '\')">
                    <a href="javascript:void(0)" class="block text-center" title="' . $escapedName . '" id="ev-brand-' . $urlKey . '">
                    ' . $imageHtml . '
                    <span class="block text-sm font-medium text-gray-700 mt-1 truncate">' . $escapedName . '</span>
                    </a>
                </div>
            </li>';
        }

        $response = [
            'status' => 'success',
            'response' => $selectHtml
        ];

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
