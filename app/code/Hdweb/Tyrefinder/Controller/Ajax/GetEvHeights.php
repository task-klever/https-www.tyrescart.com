<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;

class GetEvHeights extends Action
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
        $type = $postData['type'] ?? 'front';

        if (empty($widthValue)) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['status' => 'error', 'message' => 'Width is required']);
        }

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('height')
            ->addAttributeToFilter('width', $widthValue)
            ->addAttributeToFilter('ev', ['notnull' => true])
            ->addAttributeToFilter('ev', ['neq' => '']);
        $collection->setOrder('height', 'ASC');
        $collection->getSelect()->group('height');

        $attr = $this->productFactory->create()->getResource()->getAttribute('height');
        $attributesValue = [];

        foreach ($collection as $productData) {
            if ($attr->usesSource()) {
                $optionText = $attr->getSource()->getOptionText($productData['height']);
            }
            if (!empty($productData['height'])) {
                $attributesValue[] = [
                    'value' => $productData['height'],
                    'label' => $optionText
                ];
            }
        }

        usort($attributesValue, function ($a, $b) {
            return (float)$a['label'] - (float)$b['label'];
        });

        $selectHtml = '';
        foreach ($attributesValue as $attribute) {
            $attributeLabel = $attribute['label'];
            $optionTextLower = strtolower($attributeLabel);
            $displayLabel = ($optionTextLower == 'none') ? ucfirst($attributeLabel) : $attributeLabel;

            if (!empty($attribute['value'])) {
                $displayLabel = ($optionTextLower == 'none') ? ucfirst($attributeLabel) : $attributeLabel;
                if ($type == 'front') {
                    $selectHtml .= '<button onclick="evGetrim(' . $attribute['value'] . ',\'' . $displayLabel . '\',\'front\')" class="group relative rounded-xl border-2 px-4 py-3 text-left transition-all duration-200 border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm">
                        <span class="block text-sm font-semibold">' . $displayLabel . '</span>
                    </button>';
                } else {
                    $selectHtml .= '<button onclick="evGetRearrim(' . $attribute['value'] . ',\'' . $displayLabel . '\',\'rear\')" class="group relative rounded-xl border-2 px-4 py-3 text-left transition-all duration-200 border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm">
                        <span class="block text-sm font-semibold">' . $displayLabel . '</span>
                    </button>';
                }
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
