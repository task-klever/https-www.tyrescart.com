<?php

namespace Hdweb\Purchaseorder\Block\Adminhtml\Vendor;

class Fitment extends \Magento\Backend\Block\Widget {

    protected $_template = 'vendor/fitment.phtml';
    protected $productFactory;
    protected $scopeConfigInterface;
    protected $poVendorFitmentModel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Hdweb\Purchaseorder\Model\Povendorfitment $poVendorFitmentModel,
        array $data = []
    ) {
    	$this->productFactory = $productFactory;
    	$this->scopeConfigInterface = $scopeConfigInterface;
    	$this->poVendorFitmentModel = $poVendorFitmentModel;
        parent::__construct($context, $data);
    }

    public function getFitmentProducts() {
		$_productloader = $this->productFactory;
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$fitmentProductIds = $this->scopeConfigInterface->getValue('purchaseorder/general/po_fitment_product',$storeScope);
		$productIds = explode(',', trim($fitmentProductIds));
		
		$productCollection = array();
		foreach($productIds as $productId){
			$productCollection[] = $_productloader->create()->load($productId);
		}
		return $productCollection;
    }
	
	public function getVendorFitment($sku){
		$objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
		$povendorfitment = $this->poVendorFitmentModel;
		$collection = $povendorfitment->getCollection()
				->addFieldToSelect("*")
                ->addFilter("vendor_id", $this->getRequest()->getParam("id"))
				->addFilter("sku", $sku)
				->getFirstItem();
		$vendorprice = '';		
		if(count($collection->getData()) > 0){
			$collection->getFirstItem();
			$vendorprice = $collection->getVendorPrice();
		}				
		return $vendorprice;
	}
}