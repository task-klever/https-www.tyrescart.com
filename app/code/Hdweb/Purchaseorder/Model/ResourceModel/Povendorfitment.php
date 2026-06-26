<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel;


class Povendorfitment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	protected function _construct()
	{
		$this->_init('po_vendor_fitment', 'id');
	}
	
}