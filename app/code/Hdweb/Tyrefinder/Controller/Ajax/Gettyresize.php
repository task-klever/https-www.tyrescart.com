<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Store\Model\ScopeInterface;

class Gettyresize extends \Magento\Framework\App\Action\Action
{
    const CARTYRE_CATEGORY_ID  = 'hdweb/general/car_tyre_category_id';

    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $productFactory;
    public $scopeConfig;
    protected $_categoryFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
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
        $this->_categoryFactory = $categoryFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $car_tyre_category_id   = $this->scopeConfig->getValue(self::CARTYRE_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        $category               = $this->_categoryFactory->create()->load($car_tyre_category_id);
        $collection             = $this->productCollectionFactory->create()
            ->addAttributeToSelect('tyre_size')
            ->addAttributeToSelect('width')
            ->addAttributeToSelect('height')
            ->addAttributeToSelect('rim')
            ->addCategoryFilter($category);

        $collection->setOrder('tyre_size', 'ASC');
        $collection->getSelect()->group('tyre_size');
        $attr = $this->productFactory->create()->getResource()->getAttribute('tyre_size');
        $attrWidth = $this->productFactory->create()->getResource()->getAttribute('width');
        $attrHeight = $this->productFactory->create()->getResource()->getAttribute('height');
        $attrRim = $this->productFactory->create()->getResource()->getAttribute('rim');
        $response = array();
        $options    = "";

        // Convert collection to array
        $productsArray = [];
        foreach ($collection as $product) {
            $productsArray[] = $product;
        }

        // Custom sorting function
        usort($productsArray, function ($a, $b) {
            // Get the tyre size attribute value
            $tyreSizeA = $a->getTyreSize();
            $tyreSizeB = $b->getTyreSize();

            // Remove non-numeric characters
            $tyreSizeA = preg_replace('/\D/', '', $tyreSizeA ?? '');
            $tyreSizeB = preg_replace('/\D/', '', $tyreSizeB ?? '');

            // Compare the modified values as strings
            return strcmp($tyreSizeA, $tyreSizeB);
        });

        //foreach ($collection as $productData) {
        foreach ($productsArray as $productData) {
            if ($attr->usesSource()) {
                $optionText = $attr->getSource()->getOptionText($productData['tyre_size']);
            }
            if ($attrWidth->usesSource()) {
                $optionTextWidth = $attrWidth->getSource()->getOptionText($productData['width']);
            }
            if ($attrHeight->usesSource()) {
                $optionTextHeight = $attrHeight->getSource()->getOptionText($productData['height']);
            }
            if ($attrRim->usesSource()) {
                $optionTextRim = $attrRim->getSource()->getOptionText($productData['rim']);
            }
            $optionTextNoSpace = str_replace(' ', '', $optionText);
            $optionText1 = str_replace('R', '', $optionTextNoSpace);
            $optionText2 = str_replace('/', '', $optionText1);
            $optionText3 = str_replace('/', '', $optionTextNoSpace);
            $optionText4 = str_replace('R', '/', $optionTextNoSpace);
            $optionText5 = str_replace('/', ' ', $optionText);
            $optionText5 = str_replace('R', '', $optionText5);
            $optionTextString = $optionTextNoSpace . ' ' . $optionText2 . ' ' . $optionText3 . ' ' . $optionText4 . ' ' . $optionText5 . ' ' . $optionText;
            $options .= '<li value="' . $optionText . '" label="' . $optionText . '" data-lookup="' . $optionTextString . '" data-width="' . $optionTextWidth . '" data-height="' . $optionTextHeight . '" data-rim="' . $optionTextRim . '">' . $optionText . '</li>';
        }
        $response['response'] = $options;
        $resultJson           = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
