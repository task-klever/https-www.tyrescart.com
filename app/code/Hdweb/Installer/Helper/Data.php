<?php

namespace Hdweb\Installer\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\UrlInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	
	const GOOGLE_API_KEY 	= 'AIzaSyAgyCKwzT1BdbBAV3dQ-UILVMtTLHdwq1o'; //Google API KEY
	//const GOOGLE_API_KEY 	= 'AIzaSyArzqgYpDULZYyyzqQA1WxVAiq50_J-skw'; //Google API KEY
    protected $messageManager;
    protected $_objectManager;
    protected $_resource;
    protected $_urlBuilder;
    protected $storeManager;
    protected $categoryData;
    protected $_categoryFactory;
    protected $_customerSession;
    protected $_shipconfig;
	protected $cart;
    protected $_productRepository;
	protected $_adminUser;
	protected $_scopeConfig;
	protected $wishlistData;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\Category $categoryData, \Magento\Wishlist\Helper\Data $wishlistData, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Customer\Model\Session $customerSession, \Magento\Shipping\Model\Config $shipconfig,
        \Magento\User\Model\User $adminUser, \Magento\Checkout\Model\Cart $cart, \Magento\Catalog\Api\ProductRepositoryInterface $_productRepository
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->storeManager = $storeManager;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->messageManager = $messageManager;
        $this->_resource = $resource;
        $this->categoryData = $categoryData;
        $this->wishlistData = $wishlistData;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_shipconfig = $shipconfig;
        $this->_categoryFactory = $categoryFactory;
        $this->_customerSession = $customerSession;
        $this->_adminUser = $adminUser;
		$this->cart = $cart;
        $this->_productRepository  = $_productRepository;
    }

    public function getRedirectUrl() {
        if ($this->storeManager->getStore()->isCurrentlySecure()) {
            $url = $this->_urlBuilder->getUrl('checkout/cart/index', ['_secure' => true]);
        } else {
            $url = $this->_urlBuilder->getUrl('checkout/cart/index', ['_secure' => false]);
        }
        return $url;
    }

    public function getCategoryUrl() {
        $categoryId = $this->storeManager->getStore()->getRootCategoryId();

        $categoryData = $this->getCategory($categoryId);
        $childern = $categoryData->getChildren();
        $categorychildernData = $this->getCategory($childern);
        $categorychildernDatachildern = $categorychildernData->getChildren();
        $childCategory = explode(',', $categorychildernDatachildern);
        $childCategoryData = $this->getCategory($childCategory[0]); 

        return $this->storeManager->getStore()->getBaseUrl() . $childCategoryData->getUrlPath() . '.html';
    }

    public function getCategory($categoryId) {
        $this->categoryData = $this->_categoryFactory->create();
        $this->categoryData->load($categoryId);
        return $this->categoryData;
    }

    public function getChildren($categoryId = false) {
        if ($this->categoryData) {
            return $this->categoryData->getChildren();
        } else {
            return $this->getCategory($categoryId)->getChildren();
        }
    }

    public function getcurrentLat() {
        return $this->_customerSession->getcurrentLat();
    }

    public function getcurrentLong() {
        return $this->_customerSession->getcurrentLong();
    }

    public function getManagerList(){
        $usermodel = $this->_adminUser->getCollection()->addFieldToFilter('is_active', 1)->addFieldToFilter('detail_role.role_name','CSE');
       // $email = $usermodel->getColumnValues('email');
         $user_id = $usermodel->getColumnValues('user_id');
        $fname = $usermodel->getColumnValues('username');
        $lname = $usermodel->getColumnValues('lastname');
        $userList = array_combine($user_id,$fname);//all api user

        return $userList;
    }
	
	public function getCheckoutPickupDropofflist(){
        $pickup_option_list = array();
		$pickupTypes = array ( 0 => array ( 'slug' => '1', 'name' => 'Pick-Up', ), 1 => array ( 'slug' => '1', 'name' => 'Drop-Off', ), 2 => array ( 'slug' => '2', 'name' => 'Pick-Up + Drop-Off', ));
		$pickupTypes = json_decode(json_encode($pickupTypes), FALSE);

       $pickup_option_list[] = array('value' => '','label' => 'Pick-Up Options');
       foreach ($pickupTypes as $key => $value) {
        $pickup_option_list[]=array('value' => $value->slug,'label' => $value->name);   
       }
       return $pickup_option_list;
    }
	
	
	public function getAdminOrderVehcilelist(){
		
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$vehicle_list[] = array('value' => '','label' => 'Make'); 
			$function = 'getManufacturers';
			$params = array(
				'country' => 'AE',
				'lang' => 'en',
				'linkingTargetType' => 'P',
				'provider' => $objectManager->create('Hdweb\Productsearch\Helper\Data')::TECDOC_MANDATOR,
			);		
			$response = $objectManager->create('Hdweb\Productsearch\Helper\Data')->getTechdocApiConnection($function, $params);
			//$WheelVehicle = array();
			foreach($response->data->array as $item){
				//echo '<pre>';print_r($item->manuId);				
				$input = strtolower(preg_replace("/[^a-zA-Z]+/", "-", $item->manuName));
				$slugName = rtrim($input, "-");
				$vehicle_list[] = array('value' => $item->manuId, 'label' => $item->manuName);
			}
       return $vehicle_list;
    }
	
	public function checkPickupDiscount()
    {
		$objectManager 		=  \Magento\Framework\App\ObjectManager::getInstance();
		$cartModel 			= $objectManager->get('Magento\Checkout\Model\Cart');
		$productObj 		= $objectManager->get('Magento\Catalog\Model\Product');
        $itemsVisible   	= $cartModel->getQuote()->getItemsCollection();
        $maxPickupDiscount 	= array();
		$dateTime = $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
		$currentDate = $dateTime->gmtDate(); //2020-09-21 09:06:15 2020-09-21 00:00:00--date--2020-09-30 00:00:00
		$autoparts_category = array();
		$isServicePackage = 0;
        foreach ($itemsVisible as $key => $value) {
            $product_id 		= $value->getProductId();
            $product    		= $productObj->load($product_id);
			$partCategory 		= $product->getResource()->getAttribute("parts_category")->getFrontend()->getValue($product);
			$autoparts_category[] = $partCategory;
			if($partCategory == 'Service Package'){
				$isServicePackage = 1;
			}
			$pickupDiscountStartDate = $product->getPickupDiscountStartDate();
			$pickupDiscountEndDate = $product->getPickupDiscountEndDate();
			if($pickupDiscountStartDate != '' && $pickupDiscountEndDate != ''){
				if($pickupDiscountStartDate < $currentDate && $pickupDiscountEndDate > $currentDate){
					$maxPickupDiscount[]   = $product->getPickupDiscount();
				}else{
					continue;
				}
			}else{
				continue;
			}
        }
		$allValuesAreTheSame = (count(array_unique($autoparts_category)) === 1);
		if($allValuesAreTheSame == 1){
			if($partCategory == 'Service Package'){
				$isPickupAvailable = 1;
			}else{
				$isPickupAvailable = 0;
			}
		}else{
			$isPickupAvailable = 0;
		}
		if(count($maxPickupDiscount) > 0){
			$maxPickupDiscount = max($maxPickupDiscount);
			if($maxPickupDiscount > 100){
				$maxPickupDiscount = 100;
			}
		}
		//echo '<pre>';print_r(max($maxPickupDiscount));die;
		$pickupDiscountArray = array('pickup_discount' => $maxPickupDiscount, 'is_pickup_discount' => $isPickupAvailable, 'is_service_package' => $isServicePackage);
		return $pickupDiscountArray;
    }
	
	public function tabbyMethodAvailable()
    {
		$cartModel 			= $this->cart;
		$productObj 		= $this->_objectManager->get('Magento\Catalog\Model\Product');
        $itemsVisible   	= $cartModel->getQuote()->getItemsCollection();
		$tabbyMethodAvailable = 1;
        foreach ($itemsVisible as $key => $value) {
            $product_id 		= $value->getProductId();
            $product    		= $productObj->load($product_id);
			$tabbyMethod 		= $product->getResource()->getAttribute("tabby_method")->getFrontend()->getValue($product);
			if($tabbyMethod == 'No'){
				$tabbyMethodAvailable = 0;
			}
		}
		$appliedRuleIds = $cartModel->getQuote()->getAppliedRuleIds();
		if($appliedRuleIds){
			$appliedRuleIds = explode(',',$appliedRuleIds);
			if(count($appliedRuleIds) > 0){
				foreach($appliedRuleIds as $appliedRuleId){
					$rule = $this->_objectManager->create('Magento\SalesRule\Model\Rule')->load($appliedRuleId);
					//echo '<pre>';print_r($rule->getData());
					if($rule->getCouponType() == 2 && $rule->getColorText() == 'SpinWheel'){
						$tabbyMethodAvailable = 0;
					}
					if($rule->getCouponType() == 2 && $rule->getColorText() != 'SpinWheel'){
						$tabbyMethodAvailable = 1;
					}
					
				}
			}
		}

		return $tabbyMethodAvailable;
    }
	
	public function showVehicleInfo()
    {
		$productObj 		= $this->_objectManager->get('Magento\Catalog\Model\Product');
        $itemsVisible   	= $this->cart->getQuote()->getItemsCollection();
		$autoparts_category = array();
        foreach ($itemsVisible as $key => $value) {
            $product_id 		= $value->getProductId();
            $product    		= $productObj->load($product_id);
			$partCategory 		= $product->getResource()->getAttribute("parts_category")->getFrontend()->getValue($product);
			$autoparts_category[] = $partCategory;
        }
		$allValuesAreTheSame = (count(array_unique($autoparts_category)) === 1);
		if($allValuesAreTheSame == 1){
			if($partCategory == 'Bicycle Tyres'){
				$isVehicleInfoAvailable = 0;
			}else{
				$isVehicleInfoAvailable = 1;
			}
		}else{
			$isVehicleInfoAvailable = 1;
		}
		return $isVehicleInfoAvailable;
    }
	
	public function isStarlightProducts()
    {
		$productObj 		= $this->_objectManager->get('Magento\Catalog\Model\Product');
        $itemsVisible   	= $this->cart->getQuote()->getItemsCollection();
		$isStarlightProductsAvailable = 0;
        foreach ($itemsVisible as $key => $value) {
            $product_id 		= $value->getProductId();
            $product    		= $productObj->load($product_id);
			$partCategory 		= $product->getResource()->getAttribute("parts_category")->getFrontend()->getValue($product);
			if($partCategory == 'Starlight Headliner'){
				$isStarlightProductsAvailable = 1;
			}
        }
		return $isStarlightProductsAvailable;
    }
	
	public function getMobileVanCountByOrder($pickup_date)
    {		
		$pickup_date = date("Y-m-d", strtotime($pickup_date)); 
		$selectedDate =  $pickup_date.' 00:00:00';
		
		$orderCollectionFactory = $this->_objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
		$orderCollectionCount = $orderCollectionFactory->create()	
						->addAttributeToSelect('entity_id')
						->addFieldToFilter('pickup_store', array('in' => array(2,6)))
						->addFieldToFilter('pickup_date', ['gteq' => $selectedDate])
						->addFieldToFilter('pickup_date', ['lteq' => $selectedDate])
						->count();
		return $orderCollectionCount;				
    }
}