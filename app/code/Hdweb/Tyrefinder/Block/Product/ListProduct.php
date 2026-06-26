<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hdweb\Tyrefinder\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Store\Model\ScopeInterface;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $productCollectionFactory;
    protected $_storeManager;
    protected $request;
    protected $_defaultToolbarBlock = Toolbar::class;
	protected $productlistingHelper;
	protected $productStatus;
	protected $productVisibility;
    protected $categoryRepository;
    protected $urlHelper;
    protected $_postDataHelper;
    protected $_catalogLayer;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Hdweb\Tyrefinder\Helper\Productlisting $productlistingHelper,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
		\Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->_catalogLayer            = $layerResolver->get();
        $this->_postDataHelper          = $postDataHelper;
        $this->categoryRepository       = $categoryRepository;
        $this->urlHelper                = $urlHelper;
        $this->_scopeConfig             = $scopeConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_storeManager            = $storeManager;
        $this->request                  = $request;
		$this->productlistingHelper 	= $productlistingHelper;
		$this->productStatus 			= $productStatus;
		$this->productVisibility 		= $productVisibility;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper);
    }

    protected function _getProductCollection()
    {

        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    private function initializeProductCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId($categories->getIterator()->current()->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }
        $collection = $layer->getProductCollection();

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    public function getRearcollection()
    {
        $width_rear                  = $this->getRequest()->getParam('width_rear');
        $height_rear                 = $this->getRequest()->getParam('height_rear');
        $rim_rear                    = $this->getRequest()->getParam('rim_rear');
        $rear_tyre_search_collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
			->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
			->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addAttributeToFilter('width', $this->productlistingHelper->getOptionIdByLabel('width', $width_rear))
            ->addAttributeToFilter('height', $this->productlistingHelper->getOptionIdByLabel('height', $height_rear))
            ->addAttributeToFilter('rim', $this->productlistingHelper->getOptionIdByLabel('rim', $rim_rear));

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $rear_tyre_search_collection]
        );

        return $rear_tyre_search_collection;
    }
	
	public function getFrontcollection()
    {
        $width                  = $this->getRequest()->getParam('width');
        $height                 = $this->getRequest()->getParam('height');
        $rim                    = $this->getRequest()->getParam('rim');
        $front_tyre_search_collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('width', $width)
            ->addFieldToFilter('height', $height)
            ->addFieldToFilter('rim', $rim);

        return $front_tyre_search_collection;
    }

    public function getBundleCollection()
    {
        $allBundleItems = array();
        if ($this->isBundle()) {
            $rearcollection = $this->getRearcollection();
            //$frontcollection = $this->getFrontcollection();
            if ($this->_productCollection === null) {
                $this->_productCollection = $this->initializeProductCollection();
            }
			$frontcollection = $this->_productCollection;
            $isBundleFound  = 0;
            foreach ($frontcollection as $fronProduct) {
                $FrontbrandId   = $fronProduct->getBrand();
                $FrontpatternId = $fronProduct->getPattern();
                $FrontyearId    = $fronProduct->getYear();
                $FrontrunflatId = $fronProduct->getRunflat();
                $FrontrunOEMMarking = $fronProduct->getOemMarking();

                foreach ($rearcollection as $rearProduct) {
                    if (($FrontrunOEMMarking != $rearProduct->getOemMarking()) || ($FrontbrandId != $rearProduct->getBrand()) || ($fronProduct->getId() == $rearProduct->getId()) || ($FrontpatternId != $rearProduct->getPattern()) || ($FrontyearId != $rearProduct->getYear()) || ($FrontrunflatId != $rearProduct->getRunflat()) || ($fronProduct->getIsSalable() != $rearProduct->getIsSalable())) {
                        continue;
                    }

					$frontset2price = $this->productlistingHelper->getSet2price($fronProduct);
					$front_price = str_replace("AED","",$frontset2price);
					$front_price = str_replace(",","",$front_price);
					$rearset2price = $this->productlistingHelper->getSet2price($rearProduct);
					$rear_price = str_replace("AED","",$rearset2price);
					$rear_price = str_replace(",","",$rear_price);
					$bundleset4price = $front_price + $rear_price;

                    $allBundleItems[] = array('frontProduct' => $fronProduct, 'rearProduct' => $rearProduct, 'front' => $fronProduct->getId(), 'rear' => $rearProduct->getId(), 'bundle_price' => $bundleset4price, 'front_rating_summary' => $fronProduct->getRatingSummary(), 'rear_rating_summary' => $rearProduct->getRatingSummary());
                }
            }
			/* start bundle price sort */ 
			array_multisort(array_map(function($element) {
				return $element['bundle_price'];
			}, $allBundleItems), SORT_ASC, $allBundleItems);
			/* end bundle price sort */ 
        }
    
        return $allBundleItems;
    }

    public function isBundle()
    {
        $width_rear  = $this->getRequest()->getParam('width_rear');
        $height_rear = $this->getRequest()->getParam('height_rear');
        $rim_rear    = $this->getRequest()->getParam('rim_rear');
        $isBundle    = 0;
        if (isset($width_rear) && isset($height_rear) && isset($rim_rear) && !empty($width_rear) && !empty($height_rear) && !empty($rim_rear)) {
            $isBundle = 1;
        }

        return $isBundle;
    }
}