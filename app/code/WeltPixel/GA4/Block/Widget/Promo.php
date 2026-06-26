<?php
namespace WeltPixel\GA4\Block\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Widget\Helper\Conditions;

class Promo extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
   protected $catalogProductVisibility;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var Conditions
     */
    protected $conditionsHelper;

    /**
     * @var SqlBuilder
     */
    protected $sqlBuilder;

    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $wpHelper;


    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Rule $rule
     * @param Conditions $conditionsHelper
     * @param SqlBuilder $sqlBuilder
     * @param \WeltPixel\GA4\Helper\Data $wpHelper
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        CategoryRepositoryInterface $categoryRepository,
        Rule $rule,
        Conditions $conditionsHelper,
        SqlBuilder $sqlBuilder,
        \WeltPixel\GA4\Helper\Data $wpHelper,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productsCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->categoryRepository = $categoryRepository;
        $this->rule = $rule;
        $this->conditionsHelper = $conditionsHelper;
        $this->sqlBuilder = $sqlBuilder;
        $this->wpHelper = $wpHelper;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/promolink_widget.phtml');
    }

    public function getProductCollection()
    {
        $productCollection = $this->_productCollectionFactory->create();

        $productCollection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $productCollection = $this->_addProductAttributesAndPrices($productCollection)
            ->addStoreFilter($this->getStoreId())
            ->setCurPage(1);

        $conditions = $this->getConditions();
        if (!$conditions) {
            return null;
        }
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);
        $productCollection->distinct(true);

        return $productCollection;
    }

    /**
     * Get conditions
     *
     * @return Combine
     */
    protected function getConditions()
    {
        $conditions = $this->getData('conditions_encoded')
            ? $this->getData('conditions_encoded')
            : $this->getData('conditions');

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        if (!$conditions) {
            return null;
        }

        foreach ($conditions as $key => $condition) {
            if (!empty($condition['attribute'])) {
                if (in_array($condition['attribute'], ['special_from_date', 'special_to_date'])) {
                    $conditions[$key]['value'] = date('Y-m-d H:i:s', strtotime($condition['value']));
                }

                if ($condition['attribute'] == 'category_ids') {
                    $conditions[$key] = $this->updateAnchorCategoryConditions($condition);
                }
            }
        }

        $this->rule->loadPost(['conditions' => $conditions]);
        return $this->rule->getConditions();
    }

    /**
     * Update conditions if the category is an anchor category
     *
     * @param array $condition
     * @return array
     */
    private function updateAnchorCategoryConditions(array $condition): array
    {
        if (array_key_exists('value', $condition)) {
            $categoryId = $condition['value'];

            try {
                $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
            } catch (NoSuchEntityException $e) {
                return $condition;
            }

            $children = $category->getIsAnchor() ? $category->getChildren(true) : [];
            if ($children) {
                $children = explode(',', $children);
                $condition['operator'] = "()";
                $condition['value'] = array_merge([$categoryId], $children);
            }
        }

        return $condition;
    }

    /**
     * @return false|string
     */
    public function getItemsJson()
    {
        $products = [];
        $index = 1;
        foreach ($this->getProductCollection() as $product) {
            $productDetail = [];
            $productDetail['item_name'] = $this->wpHelper->getProductName($product);
            $productDetail['affiliation'] = $this->wpHelper->getAffiliationName();
            $productDetail['item_id'] = $this->wpHelper->getGtmProductId($product);
            $productDetail['price'] = floatval(number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));
            if ($this->wpHelper->isBrandEnabled()) {
                $productDetail['item_brand'] = $this->wpHelper->getGtmBrand($product);
            }
            $ga4Categories = $this->wpHelper->getGA4CategoriesFromCategoryIds($product->getCategoryIds());
            $productDetail = array_merge($productDetail, $ga4Categories);
            $productDetail['index'] = $index;
            $productDetail['quantity'] = 1;
            $productDetail['item_list_name'] = __('Promotion List From') . ' ' . $this->getData('promo_name');
            $productDetail['item_list_id'] = 'promotion_list';

            $index +=1;

            $products[] = $productDetail;
        }

        return json_encode($products);
    }
}
