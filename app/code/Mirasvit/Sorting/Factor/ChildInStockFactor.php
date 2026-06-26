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

class ChildInStockFactor implements FactorInterface
{
    /**
     * @var Context
     */
    protected $context;
    
    /**
     * @var FactorIndexer
     */
    protected $indexer;

    public function __construct(
        Context $context,
        FactorIndexer $indexer
    ) {
        $this->context = $context;
        $this->indexer = $indexer;
    }

    public function getName(): string
    {
        return 'Number of Child Products in Stock';
    }

    public function getDescription(): string
    {
        return 'Rank products based on the number of child products available in stock';
    }

    public function getUiComponent(): ?string
    {
        return null;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        if ($productIds) {
            return;
        }

        $result = $this->getDefaultStock();

        $this->indexer->process($rankingFactor, $productIds, function () use ($result) {
            foreach ($result as $key => $value) {
                $score = (int)$value['value'];
                $value = $value['details'];

                $this->indexer->add((int)$key, $score, (string)$value);
            }
        });
    }

    private function getDefaultStock(): array
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id']
        )->joinInner(
            ['stock' => $resource->getTableName('cataloginventory_stock_item')],
            'stock.product_id = e.entity_id',
            [
                'value'   => new \Zend_Db_Expr('COUNT(stock.product_id)'),
                'details' => new \Zend_Db_Expr( 'CONCAT_WS(" ", "Product ID in stock:", GROUP_CONCAT(stock.product_id))')
            ]
        )->where(
            'stock.qty > 0'
        )->group('e.entity_id');

        $stmt = $connection->query($select);

        $rows = $stmt->fetchAll(\PDO::FETCH_UNIQUE);

        // calculate factor based on child discounts
        // catalog_product_super_link - contains relations between configurable, bundled, grouped to simple
        $select = $connection->select();
        $select->from(['link' => $resource->getTableName('catalog_product_super_link')], [
            'entity_id' => 'parent_id',
        ])->joinLeft(
            ['stock' => $resource->getTableName('cataloginventory_stock_item')],
            'link.product_id = stock.product_id',
            [
                'value'   => new \Zend_Db_Expr('COUNT(stock.product_id)'),
                'details' => new \Zend_Db_Expr( 'CONCAT_WS(" ", "Child products IDs in stock:", GROUP_CONCAT(stock.product_id))')
            ]
        )->where(
            'stock.qty > 0'
        )->group('link.parent_id');

        foreach ($connection->query($select)->fetchAll(\PDO::FETCH_UNIQUE) as $key => $value) {
            $rows[$key]['value']   = $value['value'];
            $rows[$key]['details'] = $value['details'];
        };

        return $rows;
    }
}
