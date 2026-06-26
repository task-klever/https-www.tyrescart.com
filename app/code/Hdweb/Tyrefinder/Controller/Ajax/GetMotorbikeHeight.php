<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Store\Model\ScopeInterface;

class GetMotorbikeHeight extends \Magento\Framework\App\Action\Action
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
        $attributesValue = array();
        $type = $postData['type'];
        $attributeValueId = $postData['width'];
        $selectHtml = '';
        $response = array();

        $motorbike_category_id = $this->scopeConfig->getValue(self::MOTORBIKE_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        $category = $this->_categoryFactory->create()->load($motorbike_category_id);

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('height')
            ->addAttributeToFilter('width', $attributeValueId)
            ->addCategoryFilter($category);
        $collection->setOrder('height', 'ASC');
        $collection->getSelect()->group('height');

        $attr = $this->productFactory->create()->getResource()->getAttribute('height');

        foreach ($collection as $productData) {
            if ($attr->usesSource()) {
                $optionText = $attr->getSource()->getOptionText($productData['height']);
            }

            $selected = false;
            $item = array('value' => $productData['height'], 'label' => $optionText, 'selected' => $selected);
            $attributesValue[] = $item;
        }

        usort($attributesValue, function ($a, $b) {
            return (float)$a['label'] - (float)$b['label'];
        });

        foreach ($attributesValue as $attribute) {
            $attributeLabel = $attribute['label'];
            $optionTextLower = strtolower($attributeLabel);
            $optionTextCapital = ucfirst($attributeLabel);

            if (!empty($attribute['value'])) {
                if ($type == 'front') {
                    if ($optionTextLower == 'none') {
                        $selectHtml .= '<li class="li-search">
                            <a href="javascript:void(0)" class="" onclick="getrim(' . $attribute['value'] . ',\'' . $optionTextCapital . '\',\'front\')" id="rear-height-' . $attribute['value'] . '"><span>' . $optionTextCapital . '</span></a></li>';
                    } else {
                        $selectHtml .= '<li class="li-search">
                            <a href="javascript:void(0)" class="" onclick="getrim(' . $attribute['value'] . ',\'' . $attributeLabel . '\',\'front\')" id="rear-height-' . $attribute['value'] . '"><span>' . $attributeLabel . '</span></a></li>';
                    }
                } else {
                    if ($optionTextLower == 'none') {
                        $selectHtml .= '<li class="li-search">
                            <a href="javascript:void(0)" class="" onclick="getRearrim(' . $attribute['value'] . ',\'' . $optionTextCapital . '\',\'rear\')" id="rear-height-' . $attribute['value'] . '"><span>' . $optionTextCapital . '</span></a></li>';
                    } else {
                        $selectHtml .= '<li class="li-search">
                            <a href="javascript:void(0)" class="" onclick="getRearrim(' . $attribute['value'] . ',\'' . $attributeLabel . '\',\'rear\')" id="rear-height-' . $attribute['value'] . '"><span>' . $attributeLabel . '</span></a></li>';
                    }
                }
            }
        }

        $response['status'] = 'SUCCESS';
        $response['response'] = $selectHtml;
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
