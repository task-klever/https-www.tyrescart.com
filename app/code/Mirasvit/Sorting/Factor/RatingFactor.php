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

class RatingFactor implements FactorInterface
{
    use ScoreTrait;

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
        return 'Product Rating';
    }

    public function getDescription(): string
    {
        return 'Rank products based on overall rating.';
    }

    public function getUiComponent(): ?string
    {
        return null;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $stores = $this->storeManager->getStores();

        $this->indexer->process($rankingFactor, $productIds, function () use ($stores, $resource, $connection, $productIds) {
            foreach ($stores as $store) {
                $select = $connection->select();
                $storeId = (int) $store->getId();

                $select->from(
                    ['e' => $resource->getTableName('catalog_product_entity')],
                    ['entity_id']
                )->joinInner(
                    ['vote' => $resource->getTableName('rating_option_vote')],
                    'vote.entity_pk_value = e.entity_id',
                    ['value' => new \Zend_Db_Expr('AVG(vote.percent) - 1 + COUNT(vote.vote_id) / 1000')]
                )->joinInner(
                    ['review' => $resource->getTableName('review')],
                    'review.review_id = vote.review_id',
                    []
                )->joinInner(
                    ['review_store' => $resource->getTableName('review_store')],
                    'review_store.review_id = review.review_id'
                )->where('review.status_id=1'
                )->where('review_store.store_id=' . $storeId
                )->group('e.entity_id');
        
                if ($productIds) {
                    $select->where('e.entity_id IN (?)', $productIds);
                }
        
                $stmt = $connection->query($select);
                
                while ($row = $stmt->fetch()) {
                    $value = $row['value'];
                    
                    $score = $this->normalize((float)$value, 0, 100);
                    
                    $this->indexer->add((int)$row['entity_id'], $score, $value, $storeId);
                }
            }
        });

    }
}
