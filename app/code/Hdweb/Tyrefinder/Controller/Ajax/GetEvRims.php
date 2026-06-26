<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;

class GetEvRims extends Action
{
    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $productFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $productCollectionFactory,
        ProductFactory $productFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
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
        $widthValue = $postData['width'] ?? '';
        $heightValue = $postData['height'] ?? '';
        $type = $postData['type'] ?? 'front';

        if (empty($widthValue) || empty($heightValue)) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['status' => 'error', 'message' => 'Width and height are required']);
        }

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('rim')
            ->addAttributeToFilter('width', $widthValue)
            ->addAttributeToFilter('height', $heightValue)
            ->addAttributeToFilter('ev', ['notnull' => true])
            ->addAttributeToFilter('ev', ['neq' => ''])
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
            ]]);

        $collection->getSelect()->group('rim');

        $attr = $this->productFactory->create()->getResource()->getAttribute('rim');
        $rimOptions = [];

        foreach ($collection as $product) {
            $rimValue = $product->getData('rim');
            if (!empty($rimValue)) {
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

        $selectHtml = '';
        foreach ($rimOptions as $rimValue => $optionText) {
            if ($type == 'front') {
                $selectHtml .= '<button onclick="evSelectRim(' . $rimValue . ',\'' . $optionText . '\')" class="group relative rounded-xl border-2 px-4 py-3 text-left transition-all duration-200 border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm">
                    <span class="block text-sm font-semibold">' . $optionText . '</span>
                </button>';
            } else {
                $selectHtml .= '<button onclick="evSelectRearRim(' . $rimValue . ',\'' . $optionText . '\')" class="group relative rounded-xl border-2 px-4 py-3 text-left transition-all duration-200 border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm">
                    <span class="block text-sm font-semibold">' . $optionText . '</span>
                </button>';
            }
        }

        $response = [
            'status' => 'SUCCESS',
            'response' => $selectHtml
        ];

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
