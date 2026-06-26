<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Purchaseorder extends AbstractDb
{
	protected function _construct()
	{
		$this->_init('purchase_order', 'id');
	}
	
}