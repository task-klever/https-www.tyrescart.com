<?php
namespace Hdweb\Purchaseorder\Model;
use Magento\Framework\Model\AbstractModel;
use Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem as PurchaseorderitemResourceModel;

class Purchaseorderitem extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		//$this->_init('Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem');
		$this->_init(PurchaseorderitemResourceModel::class);
	}

	
}