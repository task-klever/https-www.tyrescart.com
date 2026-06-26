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

namespace Mirasvit\Sorting\Service;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Magento\Store\Model\StoreManagerInterface;

class ScoreFetcherService
{
    private $resource;
    private $storeManager;
    private $factorRepository;
    private $tableColumns = [
        'factor_1_score',
        'factor_1_value',
        'factor_2_score',
        'factor_2_value',
        'factor_3_score',
        'factor_3_value',
        'factor_4_score',
        'factor_4_value',
        'factor_5_score',
        'factor_5_value',
        'factor_6_score',
        'factor_6_value',
        'factor_7_score',
        'factor_7_value',
        'factor_8_score',
        'factor_8_value',
        'factor_9_score',
        'factor_9_value'
    ];

    public function __construct(
        ResourceConnection $resource,
	StoreManagerInterface $storeManager,
	RankingFactorRepository $factorRepository
    ) {
        $this->resource         = $resource;
	$this->storeManager     = $storeManager;
	$this->factorRepository = $factorRepository;
    }

    public function getProductsScoreList(array $productIds, int $storeId = 0): array
    {
        if (!count($productIds)) {
            return [];
        }

        $storeId = ($storeId > 0) ? $storeId : (int) $this->storeManager->getStore()->getId();
        $tableName = $this->resource->getTableName(IndexInterface::TABLE_NAME);
        $columns = $this->prepareTableColumns($storeId);

        $select = $this->resource->getConnection()->select();
        $select->from($tableName, $columns)
            ->joinLeft(
                [IndexInterface::TABLE_NAME.'_'. $storeId => $tableName],
                IndexInterface::TABLE_NAME.'_'. $storeId . '.product_id = '. $tableName .'.'. IndexInterface::PRODUCT_ID .' AND '.
                IndexInterface::TABLE_NAME.'_'. $storeId . ".store_id = $storeId", []
                )
            ->joinLeft(
                [IndexInterface::TABLE_NAME.'_0' => $tableName],
                IndexInterface::TABLE_NAME.'_0' . '.product_id = '. $tableName .'.'. IndexInterface::PRODUCT_ID .' AND '.
                IndexInterface::TABLE_NAME.'_0' . ".store_id = 0",
                []
            )
            ->where($tableName .'.'. IndexInterface::PRODUCT_ID . ' IN (?)', $productIds);

        $rows = $this->resource->getConnection()->fetchAll($select);

        $result = [];
        foreach ($rows as $row) {
            if (isset($result[$row[IndexInterface::PRODUCT_ID]])) {
                $result[$row[IndexInterface::PRODUCT_ID]] = array_replace($result[$row[IndexInterface::PRODUCT_ID]], array_filter($row));
            } else {
                $result[$row[IndexInterface::PRODUCT_ID]] = $row;
            }
        }

        return $result;
    }

    private function prepareTableColumns(int $storeId): array
    {
	$columns = ['index_id', 'product_id', 'store_id'];

	$this->updateTableColumnsList();

        foreach ($this->tableColumns as $columnName){
            $columns[] = 'IFNULL(mst_sorting_index_'. $storeId .'.' . $columnName . ', IFNULL(mst_sorting_index_0.' . $columnName . ', 0)) as '.
                $columnName;
        }

        return $columns;
    }

    private function updateTableColumnsList()
    {
        $factors = $this->factorRepository->getCollection()
		->addFieldToFilter('is_active', true)
		->addFieldToFilter('factor_id', ['gt' => 9]);

	foreach ($factors as $factor) {
            if (!$this->resource->getConnection()->tableColumnExists(
                    $factors->getResource()->getTable('mst_sorting_index'),
                    'factor_' . $factor->getId() . '_score'
            )) {
                continue;
            }

            $this->tableColumns[] = 'factor_' . $factor->getId() . '_score';
            $this->tableColumns[] = 'factor_' . $factor->getId() . '_value';
        }
    }
}
