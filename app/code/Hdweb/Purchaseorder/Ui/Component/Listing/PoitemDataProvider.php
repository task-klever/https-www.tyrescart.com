<?php
namespace Hdweb\Purchaseorder\Ui\Component\Listing;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class PoitemDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{

    /* protected $request;
    protected $attribute; */

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'purchase_order_item',
        $resourceModel = 'Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorderitem\Collection'
        /* \Magento\Framework\App\Request\Http $request,
        \Magento\Eav\Model\Entity\Attribute $attribute */
    ) {
        /* $this->request   = $request;
        $this->attribute = $attribute; */

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['purchase_order' => $this->getTable('purchase_order')],
            'main_table.poid = purchase_order.id',
            ['poreference_no' => 'purchase_order.poreference_no', 'comment' => 'purchase_order.comment', 'created_at' => 'purchase_order.created_at']); // TyreDescription

        $this->addFilterToMap('poreference_no', 'purchase_order.poreference_no');
        //$this->addFilterToMap('orderreference_no', 'purchase_order.orderreference_no');
        $this->addFilterToMap('created_at', 'purchase_order.created_at');
        return $this;
    }
}
