<?php
declare(strict_types=1);

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Store\Model\ScopeInterface;

class GetMotorbikeRim extends \Magento\Framework\App\Action\Action
{
    const MOTORBIKE_CATEGORY_ID = 'hdweb/general/motorbike_tyre_category_id';

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
        if ($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document') {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setHttpResponseCode(301);
            return $resultRedirect->setPath('/');
        }

        $postData = $this->getRequest()->getParams();
        $type = $postData['type'];
        $selectHtml = '';

        $motorbike_category_id = $this->scopeConfig->getValue(self::MOTORBIKE_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        $category = $this->_categoryFactory->create()->load($motorbike_category_id);

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('rim')
            ->addAttributeToSelect('width')
            ->addAttributeToSelect('height')
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
            ]])
            ->addCategoryFilter($category);

        if (!empty($postData['width'])) {
            $collection->addAttributeToFilter("width", $postData['width']);
        }

        // Handle height filter - if "None" is passed as string, find the option value
        if (!empty($postData['height'])) {
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
            $collection->addAttributeToFilter("height", $postData['height']);
        }

        $collection->load();
        $attr = $this->productFactory->create()->getResource()->getAttribute('rim');
        $rimOptions = [];

        $rimValuesToCheck = [];
        foreach ($collection as $product) {
            $rimValue = $product->getData('rim');
            if (!empty($rimValue)) {
                $rimValuesToCheck[$rimValue] = true;
            }
        }

        foreach (array_keys($rimValuesToCheck) as $rimValue) {
            $productCheckCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->addAttributeToFilter('visibility', ['in' => [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
                ]])
                ->addCategoryFilter($category);

            if (!empty($postData['width'])) {
                $productCheckCollection->addAttributeToFilter("width", $postData['width']);
            }

            if (!empty($postData['height'])) {
                $productCheckCollection->addAttributeToFilter("height", $postData['height']);
            }

            $productCheckCollection->addAttributeToFilter("rim", $rimValue);

            $productCount = $productCheckCollection->getSize();

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

        asort($rimOptions, SORT_NUMERIC);

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
