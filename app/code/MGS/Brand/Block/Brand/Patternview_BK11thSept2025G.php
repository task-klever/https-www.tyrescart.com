<?php

namespace MGS\Brand\Block\Brand;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\ViewModel\Product\Listing\PrepareProductListItem;

/**
 * Class View
 * @package Mageplaza\Shopbybrand\Block
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
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
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
        parent::__construct($context,$data);
    }

	protected function _addBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->_brandHelper->getConfig('general_settings/route');
        $brand = $this->getBrand();
		$pattern = $this->getPattern();
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
        $breadcrumbs->addCrumb(
            'pattern',
            [
                'label' => $pattern->getPattern(),
                'title' => $pattern->getPattern(),
                //'link' => $baseUrl . $brandRoute . '/' . $brand->getUrlKey() . '/' . $pattern->getUrlKey()
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
		$title = $pattern->getMetaTitle() ?: $pattern->getValue();

		$description = $pattern->getMetaDescription();
		if ($description) {
			$this->pageConfig->setDescription($description);
		}
		$keywords = $pattern->getMetaKeywords();
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
		$pattern       = $this->getPattern();
		$brandName 	   = $pattern->getBrand();
		
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
		$brandId = $pattern->getBrandId();
		$patternId = $pattern->getPatternId();

		$collection = $this->productCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$collection->addAttributeToFilter('brand', $brandId);
		$collection->addAttributeToFilter('pattern', $patternId);
        
		return $collection;
	}

	/**
     * Use our custom product collection instead of category collection
     */
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

    /**
     * Force grid mode so $imageId is always set
     * (You can change this to detect list/grid toggle if you want)
     */
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

	/**
     * Expose Hyvä's PrepareProductListItem ViewModel to PHTML
     */
    public function getProductListItemViewModel()
    {
        return $this->productListItemViewModel;
    }
	
	public function getListProductBlock()
	{
		return $this->getLayout()->createBlock(\Magento\Catalog\Block\Product\ListProduct::class);
	}
}
