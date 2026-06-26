<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Model\Customer\Indexer;

use Magento\Customer\Model\ResourceModel\Customer\Indexer\Collection;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Traversable;

/**
 * Customers data batch generator for customer_grid indexer
 */
class Source implements \IteratorAggregate, \Countable, SourceProviderInterface
{
    /**
     * @var Collection
     */
    private $customerCollection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Indexer\CollectionFactory $collection
     * @param int $batchSize
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\Indexer\CollectionFactory $collectionFactory,
        $batchSize = 10000
    ) {
        $this->customerCollection = $collectionFactory->create();
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainTable()
    {
        return $this->customerCollection->getMainTable();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFieldName()
    {
        return $this->customerCollection->getIdFieldName();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToSelect($fieldName, $alias = null)
    {
        $this->customerCollection->addFieldToSelect($fieldName, $alias);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelect()
    {
        return $this->customerCollection->getSelect();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        $this->customerCollection->addFieldToFilter($attribute, $condition);
        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->customerCollection->getSize();
    }

    /**
     * Retrieve an iterator
     *
     * @return Traversable
     */
    public function getIterator(): \Traversable
    {
        $this->customerCollection->setPageSize($this->batchSize);
        $lastPage = $this->customerCollection->getLastPageNumber();
        $pageNumber = 0;
        do {
            $this->customerCollection->clear();
            $this->customerCollection->setCurPage($pageNumber);
            foreach ($this->customerCollection->getItems() as $key => $value) {
                yield $key => $value;
            }
            $pageNumber++;
        } while ($pageNumber <= $lastPage);
    }

    public function addAttributeToSelect($fieldName, $alias = null)
    {
        $this->customerCollection->addAttributeToSelect($fieldName, $alias);
        return $this;
    }

    public function joinAttribute($alias, $attribute, $bind, $filter = null, $joinType = 'inner', $storeId = null) 
    {
        $this->customerCollection->joinAttribute($alias, $attribute,$bind,$filter,$joinType,$storeId);
        return $this;
    }
}
