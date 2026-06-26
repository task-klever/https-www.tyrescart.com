<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.3.15
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterfaceFactory;
use Mirasvit\Sorting\Factor\FactorInterface;
use Mirasvit\Sorting\Model\ResourceModel\RankingFactor\CollectionFactory;

class RankingFactorRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var RankingFactorInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FactorInterface[]
     */
    private $pool;

    /**
     * RankingFactorRepository constructor.
     *
     * @param EntityManager                 $entityManager
     * @param RankingFactorInterfaceFactory $factory
     * @param CollectionFactory             $collectionFactory
     * @param array                         $pool
     */
    public function __construct(
        EntityManager $entityManager,
        RankingFactorInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        array $pool = []
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->pool              = $pool;
    }

    /**
     * @return \Mirasvit\Sorting\Model\ResourceModel\RankingFactor\Collection|RankingFactorInterface[]
     */
    public function getCollection()
    {
        $collection = $this->getFullCollection();

        $resource = $collection->getResource();
        $query    = $resource->getConnection()->query(
            'SHOW COLUMNS FROM '
            . $resource->getTable(IndexInterface::TABLE_NAME)
            . ' LIKE "factor_%_score"'
        );

        $indexedIds = [];

        foreach($query->fetchAll() as $column) {
            $columnName = $column['Field'];
            $id = str_replace('factor_', '', $columnName);
            $id = str_replace('_score', '', $id);

            $indexedIds[] = (int)$id;
        }

        $collection->addFieldToFilter(RankingFactorInterface::ID, ['in' => $indexedIds]);

        return $collection;
    }

    /**
     * @return \Mirasvit\Sorting\Model\ResourceModel\RankingFactor\Collection|RankingFactorInterface[]
     */
    public function getFullCollection()
    {
        $collection = $this->collectionFactory->create();

        // factors with the type "formula" always in the end
        $collection->getSelect()
            ->columns(['type_score' => 'IF(type = "formula", 1, 0)'])
            ->order('type_score ASC');

        return $collection;
    }

    /**
     * @return FactorInterface[]
     */
    public function getFactors()
    {
        return $this->pool;
    }

    /**
     * @param string $type
     *
     * @return FactorInterface|bool
     */
    public function getFactor($type)
    {
        foreach ($this->getFactors() as $identifier => $factor) {
            if ($identifier == $type) {
                return $factor;
            }
        }

        return false;
    }

    /**
     * @return RankingFactorInterface
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * @param int $id
     *
     * @return RankingFactorInterface|bool
     */
    public function get($id)
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : false;
    }

    /**
     * @param RankingFactorInterface $model
     *
     * @return RankingFactorInterface
     */
    public function save(RankingFactorInterface $model)
    {
        $this->entityManager->save($model);

        return $model;
    }

    /**
     * @param RankingFactorInterface $model
     *
     * @return $this
     */
    public function delete(RankingFactorInterface $model)
    {
        $this->entityManager->delete($model);

        return $this;
    }

    /**
     * @param string $type
     *
     * @return RankingFactorInterface|\Magento\Framework\DataObject
     */
    public function getByType($type)
    {
        return $this->getCollection()->addFieldToFilter('type', $type)->getFirstItem();
    }
}
