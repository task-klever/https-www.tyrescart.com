<?php
declare(strict_types=1);

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Store\Model\ScopeInterface;

class GetOffset extends \Magento\Framework\App\Action\Action
{
    const CARWHEELS_CATEGORY_ID = 'hdweb/general/car_wheels_category_id';

    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $productFactory;
    protected $scopeConfig;
    protected $categoryFactory;

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

        $postData = $this->getRequest()->getParams();
        $type = $postData['type'] ?? 'front';
        $selectHtml = '';

        $categoryId = $this->scopeConfig->getValue(self::CARWHEELS_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        $category = $this->categoryFactory->create()->load($categoryId);

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('offset')
            ->addCategoryFilter($category)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
            ]]);

        if (!empty($postData['width'])) {
            $collection->addAttributeToFilter('width', $postData['width']);
        }
        if (!empty($postData['rim'])) {
            $collection->addAttributeToFilter('rim', $postData['rim']);
        }

        $collection->load();
        $attr = $this->productFactory->create()->getResource()->getAttribute('offset');
        $offsetOptions = [];

        foreach ($collection as $product) {
            $offsetValue = $product->getData('offset');
            if (!empty($offsetValue) && !isset($offsetOptions[$offsetValue])) {
                $optionText = '';
                if ($attr && $attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($offsetValue);
                }
                if (!empty($optionText)) {
                    $offsetOptions[$offsetValue] = $optionText;
                }
            }
        }

        // Sort by numeric value
        asort($offsetOptions, SORT_NUMERIC);

        foreach ($offsetOptions as $offsetValue => $optionText) {
            if ($type == 'front') {
                $selectHtml .= '<li class="li-search">
                    <a href="javascript:void(0)" onclick="selectOffset(' . $offsetValue . ',\'' . $optionText . '\')" id="front-offset-' . $offsetValue . '">
                        <span>' . $optionText . '</span></a></li>';
            } else {
                $selectHtml .= '<li class="li-search">
                    <a href="javascript:void(0)" onclick="selectRearOffset(' . $offsetValue . ',\'' . $optionText . '\')" id="rear-offset-' . $offsetValue . '">
                        <span>' . $optionText . '</span></a></li>';
            }
        }

        $response['status'] = 'SUCCESS';
        $response['response'] = $selectHtml;
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
