<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel\Povendorfitment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Hdweb\Purchaseorder\Model\Povendorfitment', 'Hdweb\Purchaseorder\Model\ResourceModel\Povendorfitment');
	}

}
