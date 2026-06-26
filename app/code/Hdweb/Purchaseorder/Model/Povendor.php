<?php
namespace Hdweb\Purchaseorder\Model;

class Povendor extends \Magento\Framework\Model\AbstractModel
{


	protected function _construct()
	{
		$this->_init('Hdweb\Purchaseorder\Model\ResourceModel\Povendor');
	}

	
}