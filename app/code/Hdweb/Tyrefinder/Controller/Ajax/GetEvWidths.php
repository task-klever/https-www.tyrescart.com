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

class GetEvWidths extends Action
{
    const CARTYRE_CATEGORY_ID = 'hdweb/general/car_tyre_category_id';

    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $productFactory;
    protected $scopeConfig;
    protected $categoryFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory
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
        if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'document') {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setHttpResponseCode(301);
            return $resultRedirect->setPath('/');
        }

        $carTyreCategoryId = $this->scopeConfig->getValue(self::CARTYRE_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        $category = $this->categoryFactory->create()->load($carTyreCategoryId);

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('width')
            ->addAttributeToFilter('ev', ['notnull' => true])
            ->addAttributeToFilter('ev', ['neq' => ''])
            ->addCategoryFilter($category);

        $collection->setOrder('width', 'ASC');
        $collection->getSelect()->group('width');

        $attr = $this->productFactory->create()->getResource()->getAttribute('width');
        $attributesValue = [];

        foreach ($collection as $productData) {
            if ($attr->usesSource()) {
                $optionText = $attr->getSource()->getOptionText($productData['width']);
            }
            if (!empty($productData['width'])) {
                $attributesValue[] = [
                    'value' => $productData['width'],
                    'label' => $optionText
                ];
            }
        }

        usort($attributesValue, function ($a, $b) {
            return (float)$a['label'] - (float)$b['label'];
        });

        $fronthtml = '';
        $rearhtml = '';
        foreach ($attributesValue as $attribute) {
            $val = $attribute['value'];
            $label = $attribute['label'];
            $fronthtml .= '<button onclick="evGetheight(\''.$val.'\',\''.$label.'\',\'front\')" class="group relative rounded-xl border-2 px-4 py-3 text-left transition-all duration-200 border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm">
                <span class="block text-sm font-semibold">'.$label.'</span>
            </button>';
            $rearhtml .= '<button onclick="evGetRearheight(\''.$val.'\',\''.$label.'\',\'rear\')" class="group relative rounded-xl border-2 px-4 py-3 text-left transition-all duration-200 border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm">
                <span class="block text-sm font-semibold">'.$label.'</span>
            </button>';
        }

        $response = [
            'status' => 'success',
            'fronthtml' => $fronthtml,
            'rearhtml' => $rearhtml
        ];

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
