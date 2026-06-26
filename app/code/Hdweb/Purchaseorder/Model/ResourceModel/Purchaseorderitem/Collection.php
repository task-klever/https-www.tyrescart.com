<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Hdweb\Purchaseorder\Model\Purchaseorderitem as PurchaseorderitemModel;
use Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem as PurchaseorderitemResourceModel;

class Collection extends AbstractCollection
{
	
	protected function _construct()
	{
		//$this->_init('Hdweb\Purchaseorder\Model\Purchaseorderitem', 'Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem');
		$this->_init(PurchaseorderitemModel::class, PurchaseorderitemResourceModel::class);
	}
	protected function _initSelect()
    {
		$this->addFilterToMap('comment', 'main_table.comment');
		$this->addFilterToMap('vendor_name', 'main_table.vendor_name');
		$this->addFilterToMap('po_type', 'main_table.po_type');
		$this->addFilterToMap('poreference_no', 'main_table.poreference_no');
		$this->addFilterToMap('created_at', 'main_table.created_at');
		$this->addFilterToMap('order_id', 'main_table.order_id');
		
        parent::_initSelect();
			
		$this->getSelect()
            ->joinLeft(
                ['po' => $this->getTable('purchase_order')],
                'main_table.poid = po.id',
                array('po.comment as comment')
            ); 
    }

}
