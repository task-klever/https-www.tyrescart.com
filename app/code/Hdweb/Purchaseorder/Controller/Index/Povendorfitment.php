<?php
namespace Hdweb\Purchaseorder\Controller\Index;

class Povendorfitment extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $_povendorfitment;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Hdweb\Purchaseorder\Model\Povendorfitment $povendorfitment
    ) {
        parent::__construct($context);
        $this->resultJsonFactory        = $resultJsonFactory;
        $this->_povendorfitment = $povendorfitment;
        
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $vendor_id = $postData['vendor_id'];
        $collection = $this->_povendorfitment->getCollection()->addFieldToSelect("*")
						->addFilter("vendor_id", $vendor_id);
		$vendorFitment = array();		
		if(count($collection->getData()) > 0){
			foreach($collection as $fitmentData){
				$sku = $fitmentData->getSku();
				$vendorFitment[$sku] = $fitmentData->getVendorPrice();
			}
		}	
        $resultJson          = $this->resultJsonFactory->create();
        return $resultJson->setData($vendorFitment);
    }
}
