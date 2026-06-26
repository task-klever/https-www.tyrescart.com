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

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;
use Magento\Store\Model\StoreManagerInterface;

class BestsellerFactor implements FactorInterface
{
    use ScoreTrait;

    const ZERO_POINT = 'zero_point';

    private $context;

    private $indexer;

    private $storeManager;

    public function __construct(
        Context $context,
        FactorIndexer $indexer,
        StoreManagerInterface $storeManager
    ) {
        $this->context = $context;
        $this->indexer = $indexer;
        $this->storeManager = $storeManager;
    }

    public function getName(): string
    {
        return 'Bestsellers';
    }

    public function getDescription(): string
    {
        return 'Rank products based on the number of purchases within the period.';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_bestseller';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        if ($productIds) {
            return;
        }

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $stores = $this->storeManager->getStores();

        $zeroPoint = $rankingFactor->getConfigData(self::ZERO_POINT, 60);

        $this->indexer->process($rankingFactor, $productIds, function () use ($resource, $connection, $zeroPoint, $stores) {
            foreach ($stores as $store) {
                $select = $connection->select();
                $storeId = (int) $store->getId();

                $date = date('Y-m-d', strtotime('-' . $zeroPoint . ' day', time()));

                $selectA = $connection->select();
                $selectA->from(
                    $resource->getTableName('sales_order_item'),
                    [
                        'product_id',
                        'value' => new \Zend_Db_Expr('SUM(qty_ordered)'),
                    ]
                )->where(
                    'created_at >= ?',
                    $date
                )->where('store_id = ' . $storeId)
                    ->group('product_id');

                $selectB = $connection->select();
                $selectB->from(
                    ['i' => $resource->getTableName('sales_order_item')],
                    [
                        'product_id' => 'l.parent_id',
                        'value'      => new \Zend_Db_Expr('SUM(qty_ordered)'),
                    ]
                )->joinLeft(
                    ['l' => $resource->getTableName('catalog_product_super_link')],
                    'l.product_id = i.product_id',
                    []
                )
                    ->where('created_at >= ?', $date)
                    ->where('parent_id > 0')
                    ->where('store_id = ' . $storeId)
                    ->group('parent_id');


                $select = $connection->select()
                    ->from(
                        $connection->select()->union([$selectA, $selectB]),
                        [
                            'product_id',
                            'value' => new \Zend_Db_Expr('SUM(value)'),
                        ]
                    )
                    ->group('product_id');


                $max = (float)$connection->fetchOne(
                    $connection->select()->from($select, ['MAX(value)'])
                );
                
                $stmt = $connection->query($select);

                while ($row = $stmt->fetch()) {
                    if (empty($row['product_id'])) {
                        continue;
                    }

                    $value = $row['value'];

                    $score = $this->normalize((float)$value, 0, $max);

                    $value = 'Qty: ' . $value . '; ordered during last ' . $zeroPoint . ' days';

                    $this->indexer->add((int)$row['product_id'], $score, $value, $storeId);
                }
            }
        });
    }
}

