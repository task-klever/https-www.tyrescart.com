<?php
declare(strict_types=1);

namespace Hdweb\Tyrefinder\Controller\Ajax;

class Getrim extends \Magento\Framework\App\Action\Action
{
	const CARTYRE_CATEGORY_ID  = 'hdweb/general/car_tyre_category_id';
	const CARWHEELS_CATEGORY_ID = 'hdweb/general/car_wheels_category_id';

	protected $resultJsonFactory;
	protected $productCollectionFactory;
	protected $productFactory;
	protected $scopeConfig;
	protected $categoryFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
    	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory
	) {
    	$this->resultJsonFactory = $resultJsonFactory;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->productFactory = $productFactory;
		$this->scopeConfig = $scopeConfig;
		$this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }
    
    public function execute()
	{
		if ($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document') {
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setHttpResponseCode(301);
			return $resultRedirect->setPath('/');
		}

		$postData = $this->getRequest()->getParams();
		$type = $postData['type'];
		$selectHtml = '';

		$categoryParam = $this->getRequest()->getParam('category');
		if ($categoryParam === 'car-wheels') {
			$categoryId = $this->scopeConfig->getValue(self::CARWHEELS_CATEGORY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		} else {
			$categoryId = $this->scopeConfig->getValue(self::CARTYRE_CATEGORY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		}
		$category = $this->categoryFactory->create()->load($categoryId);

		$collection = $this->productCollectionFactory->create()
			->addAttributeToSelect('*')
			->addAttributeToSelect('rim')
			->addAttributeToSelect('width')
			->addAttributeToSelect('height')
			->addCategoryFilter($category)
			->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
			->addAttributeToFilter('visibility', ['in' => [
				\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
				\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
				\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
			]]);

		if (!empty($postData['width'])) {
			$collection->addAttributeToFilter("width", $postData['width']);
		}
		
		// Handle height filter - if "None" is passed as string, find the option value
		if (!empty($postData['height'])) {
			// If height parameter is the string "None", find the actual option value
			if (strtolower(trim((string)$postData['height'])) === 'none') {
				$heightAttr = $this->productFactory->create()->getResource()->getAttribute('height');
				if ($heightAttr && $heightAttr->usesSource()) {
					$allOptions = $heightAttr->getSource()->getAllOptions();
					foreach ($allOptions as $option) {
						if (isset($option['value']) && isset($option['label']) && 
							strtolower(trim((string)$option['label'])) === 'none') {
							$postData['height'] = $option['value'];
							break;
						}
					}
				}
			}
			
			// Filter by height value - when "None", filter by the "None" option value
			$collection->addAttributeToFilter("height", $postData['height']);
		}

		// Load products and collect unique rim values
		$collection->load();
		$attr = $this->productFactory->create()->getResource()->getAttribute('rim');
		$rimOptions = [];

		// Collect all rim values from actual products that match criteria
		$rimValuesToCheck = [];
		foreach ($collection as $product) {
			$rimValue = $product->getData('rim');
			if (!empty($rimValue)) {
				$rimValuesToCheck[$rimValue] = true;
			}
		}

		// Verify each rim option has products with exact criteria
		foreach (array_keys($rimValuesToCheck) as $rimValue) {
			// Create a fresh collection to verify products exist for this specific rim
			$productCheckCollection = $this->productCollectionFactory->create()
				->addAttributeToSelect('entity_id')
				->addCategoryFilter($category)
				->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
				->addAttributeToFilter('visibility', ['in' => [
					\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
					\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
					\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
				]]);

			if (!empty($postData['width'])) {
				$productCheckCollection->addAttributeToFilter("width", $postData['width']);
			}

			// Always filter by height value - when "None", filter by the "None" option value
			// This ensures we only verify products where height is actually "None"
			if (!empty($postData['height'])) {
				$productCheckCollection->addAttributeToFilter("height", $postData['height']);
			}

			$productCheckCollection->addAttributeToFilter("rim", $rimValue);
			
			// Use getSize() to get the actual count from database
			$productCount = $productCheckCollection->getSize();

			// Only include rim option if products actually exist
			if ($productCount > 0) {
				$optionText = '';
				if ($attr && $attr->usesSource()) {
					$optionText = $attr->getSource()->getOptionText($rimValue);
				}

				if (!empty($optionText)) {
					$rimOptions[$rimValue] = $optionText;
				}
			}
		}

		// Sort rim options: numeric first (12, 13, 14...), non-numeric last (14C, 16C, 17.5...)
		uasort($rimOptions, function ($a, $b) {
			$aIsNumeric = is_numeric($a);
			$bIsNumeric = is_numeric($b);
			if ($aIsNumeric && !$bIsNumeric) return -1;
			if (!$aIsNumeric && $bIsNumeric) return 1;
			return (float)$a - (float)$b;
		});

		// Build HTML after sorting
		foreach ($rimOptions as $rimValue => $optionText) {
			if ($type == 'front') {
				$selectHtml .= '<li class="li-search">
					<a href="javascript:void(0)" onclick="selectRim(' . $rimValue . ',\'' . $optionText . '\')" id="front-width-' . $rimValue . '">
						<span>' . $optionText . '</span></a></li>';
			} else {
				$selectHtml .= '<li class="li-search">
					<a href="javascript:void(0)" onclick="selectRearRim(' . $rimValue . ',\'' . $optionText . '\')" id="rear-width-' . $rimValue . '">
						<span>' . $optionText . '</span></a></li>';
			}
		}

		$response['status'] = 'SUCCESS';
		$response['response'] = $selectHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
	}

}