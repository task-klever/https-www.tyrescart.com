<?php

namespace Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder\Grid;
 
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
// your mane table collection
use Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder\Collection as GridCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends GridCollection implements SearchResultInterface
{
   
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }
 
    
    public function getAggregations()
    {
        return $this->aggregations;
    }
 
   
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }
	
	public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }
    
    public function getSearchCriteria()
    {
        return null;
    }
 
  
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        return $this;
    }
 
    public function getTotalCount()
    {
        return $this->getSize();
    }
 
    public function setTotalCount($totalCount)
    {
        return $this;
    }
 
  
    public function setItems(array $items = null)
    {
        return $this;
    }
}