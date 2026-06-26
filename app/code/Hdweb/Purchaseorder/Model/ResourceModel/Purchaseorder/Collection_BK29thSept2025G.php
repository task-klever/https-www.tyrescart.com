<?php
namespace Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $storeManager;
    /**
     * @var string
     */    protected $_idFieldName = 'id';

    /**
     * @param EntityFactoryInterface $entityFactory,
     * @param LoggerInterface        $logger,
     * @param FetchStrategyInterface $fetchStrategy,
     * @param ManagerInterface       $eventManager,
     * @param StoreManagerInterface  $storeManager,
     * @param AdapterInterface       $connection,
     * @param AbstractDb             $resource
     */ 
	 public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init('Hdweb\Purchaseorder\Model\Purchaseorder', 'Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
    
    protected function _initSelect()
    {
		$this->addFilterToMap('poqty', 'main_table.poqty');
		$this->addFilterToMap('vendor_name', 'main_table.vendor_name');
		$this->addFilterToMap('po_type', 'main_table.po_type');
		$this->addFilterToMap('created_at', 'main_table.created_at');
		
        parent::_initSelect();

		$this->getSelect()
            ->joinLeft(
                ['poitem' => $this->getTable('purchase_order_item')],
                'main_table.id = poitem.poid',
                array('poitem.qty')
            )
            ->group('main_table.id')
            ->columns('SUM(poitem.qty) as poqty');
			
		/* $this->getSelect()
            ->joinLeft(
                ['povendor' => $this->getTable('po_vendor')],
                'main_table.vendor = povendor.id',
                array('povendor.name as vendorname')
            ); */	
    }
}
