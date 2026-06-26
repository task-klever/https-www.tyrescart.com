<?php
namespace Hdweb\Purchaseorder\Model;
use Magento\Framework\Model\AbstractModel;
use Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder as PurchaseorderResourceModel;

class Purchaseorder extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		//$this->_init('Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder');
		$this->_init(PurchaseorderResourceModel::class);
	}

	
}