<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Store\Model\ScopeInterface;

class GetWidth extends \Magento\Framework\App\Action\Action
{	
	const CARTYRE_CATEGORY_ID  = 'hdweb/general/car_tyre_category_id';
	const CARWHEELS_CATEGORY_ID = 'hdweb/general/car_wheels_category_id';

	protected $resultJsonFactory;
	protected $productCollectionFactory;
	protected $productFactory;
	public $scopeConfig;
	protected $_categoryFactory;
	
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
		$this->_categoryFactory = $categoryFactory;

        parent::__construct($context);
    }
    
    public function execute()
    {	
		if($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document'){
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setHttpResponseCode(301);
    		return $resultRedirect->setPath('/');
		}
		
		
    	$current_category_id = "";
        $attributesValue     = array();
        $categoryParam = $this->getRequest()->getParam('category');
        if ($categoryParam === 'car-wheels') {
            $categoryId = $this->scopeConfig->getValue(self::CARWHEELS_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        } else {
            $categoryId = $this->scopeConfig->getValue(self::CARTYRE_CATEGORY_ID, ScopeInterface::SCOPE_STORE);
        }
        $category            = $this->_categoryFactory->create()->load($categoryId);
        $collection          = $this->productCollectionFactory->create()
            ->addAttributeToSelect('width')
            ->addCategoryFilter($category);

        $collection->setOrder('width', 'ASC');
        $collection->getSelect()->group('width');

        $attr = $this->productFactory->create()->getResource()->getAttribute('width');

        foreach ($collection as $productData) {
            if ($attr->usesSource()) {
                $optionText = $attr->getSource()->getOptionText($productData['width']);
            }

            $selected          = false;
            $item              = array('value' => $productData['width'], 'label' => $optionText, 'selected' => $selected);
            $attributesValue[] = $item;
        }

		
		usort($attributesValue, function ($a, $b) {
			$aIsNumeric = is_numeric($a['label']);
			$bIsNumeric = is_numeric($b['label']);
			// Numeric values first, non-numeric (31X, 33X etc.) last
			if ($aIsNumeric && !$bIsNumeric) return -1;
			if (!$aIsNumeric && $bIsNumeric) return 1;
			return (float)$a['label'] - (float)$b['label'];
		});

        $fronthtml = '';
		$rearhtml = '';
		foreach ($attributesValue as $attribute) {
			$val = "'".$attribute['value']."'";
			$label = "'".$attribute['label']."'";
			$front = "'front'";
			$rear = "'rear'";
			$fronthtml .= '<li class="li-search">
						<a href="javascript:void(0)" class="" title="'.$attribute['label'].'" onclick="getheight('.$val.','.$label.','.$front.')" id="front-width-'.$attribute['value'].'"><span>'.$attribute['label'].'</span></a>
					</li>';
			$rearhtml .= '<li class="li-search">
						<a href="javascript:void(0)" class="" title="'.$attribute['label'].'" onclick="getRearheight('.$val.','.$label.','.$rear.')" id="rear-width-'.$attribute['value'].'"><span>'.$attribute['label'].'</span></a>
					</li>';
		}
        
        $response['status'] = 'success';
		$response['fronthtml'] = $fronthtml;
		$response['rearhtml'] = $rearhtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}

