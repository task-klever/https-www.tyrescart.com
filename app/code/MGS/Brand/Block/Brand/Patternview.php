<?php

namespace MGS\Brand\Block\Brand;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class Patternview
 * @package MGS\Brand\Block\Brand
 */
class Patternview extends \Magento\Framework\View\Element\Template
{
    protected $objectManager;
    protected $helper;
    protected $_coreRegistry;
    protected $brandModel;
    protected $productCollectionFactory;
    protected $_brandHelper;
    protected $productRepository;
    protected $listProductBlock;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \MGS\Brand\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \MGS\Brand\Model\Brand $brandModel,
        CollectionFactory $productCollectionFactory,
        \MGS\Brand\Helper\Data $brandHelper,
        ProductRepositoryInterface $productRepository,
        ListProduct $listProductBlock,
        array $data = []
    ) {
        $this->objectManager = $objectmanager;
        $this->helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->brandModel = $brandModel;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_brandHelper = $brandHelper;
        $this->productRepository = $productRepository;
        $this->listProductBlock = $listProductBlock;
        parent::__construct($context, $data);
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->_brandHelper->getConfig('general_settings/route');

        $brand = $this->getBrand();
        $pattern = $this->getPattern();

        if (!$breadcrumbs || !$brand || !$pattern) {
            return;
        }

        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );

        $breadcrumbs->addCrumb(
            'brand',
            [
                'label' => $brand->getName(),
                'title' => $brand->getName(),
                'link' => $baseUrl . $brandRoute . '/' . $brand->getUrlKey()
            ]
        );

        // Handle array/object pattern safely
        $patternName = is_array($pattern)
            ? ($pattern['pattern'] ?? '')
            : ($pattern->getPattern() ?? '');

        $breadcrumbs->addCrumb(
            'pattern',
            [
                'label' => $patternName,
                'title' => $patternName,
                'link' => ''
            ]
        );
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pattern = $this->getPattern();

        // ✅ Safe handling for array or object
        if (is_array($pattern)) {
            $title = $pattern['meta_title'] ?? ($pattern['value'] ?? '');
            $description = $pattern['meta_description'] ?? '';
            $keywords = $pattern['meta_keywords'] ?? '';
        } elseif ($pattern) {
            $title = $pattern->getMetaTitle() ?: $pattern->getValue();
            $description = $pattern->getMetaDescription();
            $keywords = $pattern->getMetaKeywords();
        } else {
            return $this;
        }

        if ($description) {
            $this->pageConfig->setDescription($description);
        }

        if ($keywords) {
            $this->pageConfig->setKeywords($keywords);
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }

        $this->_addBreadcrumbs();

        return $this;
    }

    public function getPattern()
    {
        return $this->_coreRegistry->registry('current_pattern');
    }

    public function getBrand()
    {
        $pattern = $this->getPattern();

        if (!$pattern) {
            return null;
        }

        // Handle array/object
        $brandName = is_array($pattern)
            ? ($pattern['brand'] ?? '')
            : $pattern->getBrand();

        $brand = $this->brandModel->getCollection()
            ->addFieldToFilter('name', $brandName)
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->getFirstItem();

        return $brand;
    }

    public function getPatternProducts()
    {
        $pattern = $this->getPattern();

        if (!$pattern) {
            return [];
        }

        // Handle array/object
        $brandId = is_array($pattern)
            ? ($pattern['brand_id'] ?? '')
            : $pattern->getBrandId();

        $patternId = is_array($pattern)
            ? ($pattern['pattern_id'] ?? '')
            : $pattern->getPatternId();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('brand', $brandId)
            ->addAttributeToFilter('pattern', $patternId);

        return $collection;
    }

    protected function _getProductCollection()
    {
        return $this->getData('custom_collection') ?: parent::_getProductCollection();
    }

    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMode()
    {
        return 'grid';
    }

    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['neq' => 1])
            ->setPageSize(10);

        return $collection;
    }

    public function getItemHtml($product)
    {
        /** @var \Magento\Catalog\Block\Product\ListProduct $listBlock */
        $listBlock = $this->layout->createBlock(\Magento\Catalog\Block\Product\ListProduct::class);
        return $listBlock->getItemHtml($product);
    }

    public function getProductListItemViewModel()
    {
        return $this->productListItemViewModel ?? null;
    }

    public function getListProductBlock()
    {
        return $this->getLayout()->createBlock(\Magento\Catalog\Block\Product\ListProduct::class);
    }
}
