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

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Magento\Store\Model\StoreManagerInterface;

/** @SuppressWarnings(PHPMD) */
class CriteriaApplierService
{
    const FLAG_NO_SORT   = 'NO_SORT';
    const FLAG_GLOBAL    = 'sorting_global';
    const FLAG_CRITERION = 'sorting_criterion';
    const FLAG_DIRECTION = 'sorting_direction';

    private $rankingFactorRepository;

    private $configProvider;

    private $collectionService;

    private $criterionRepository;

    private $currentCriterion;

    private $request;

    private $storeManager;

    private $criteriaManagenentService;

    public function __construct(
        RankingFactorRepository $rankingFactorRepository,
        ConfigProvider $configProvider,
        CriterionRepository $criterionRepository,
        Collection\CollectionService $collectionService,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        CriteriaManagementService $criteriaManagementService
    ) {
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->configProvider          = $configProvider;
        $this->collectionService       = $collectionService;
        $this->criterionRepository     = $criterionRepository;
        $this->request                 = $request;
        $this->storeManager            = $storeManager;

        $this->criteriaManagenentService = $criteriaManagementService;
    }

    public function setGlobalRankingFactors(AbstractCollection $collection): AbstractCollection
    {
        $rankingFactors = $this->rankingFactorRepository->getCollection();
        $rankingFactors->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(RankingFactorInterface::IS_GLOBAL, 1);

        if ($rankingFactors->getSize()) {
            $collection->setFlag(self::FLAG_GLOBAL, true);
        }

        return $collection;
    }

    public function setCriterion(AbstractCollection $collection, CriterionInterface $criterion, string $dir = null): AbstractCollection
    {
        if ($dir !== null && strtolower($dir) !== 'asc' && strtolower($dir) !== 'desc') {
            $dir = null;
        }

        $collection->setFlag(self::FLAG_CRITERION, $criterion);
        $collection->setFlag(self::FLAG_DIRECTION, $dir);

        $this->currentCriterion = $criterion;

        return $collection;
    }

    public function getCurrentCriterion(): ?CriterionInterface
    {
        return $this->currentCriterion;
    }

    public function sortCollection(AbstractCollection $collection): AbstractCollection
    {
        // to avoid conflict with the Advanced Product Feeds
        if ($collection->getFlag(CriteriaApplierService::FLAG_NO_SORT)) {
            return $collection;
        }

        if (strpos($this->request->getFullActionName(), 'checkout') !== false) {
            return $collection;
        }

        $isGrapqlRequest = strpos($this->request->getRequestUri(), 'graphql') !== false;

        if ((bool)$collection->getFlag(self::FLAG_CRITERION) === false
            && ($this->configProvider->isApplySortingForCustomBlocks((int)$this->storeManager->getStore()->getId()) === false || $isGrapqlRequest)) {
            return $collection;
        }

        if (
            (bool)$collection->getFlag(self::FLAG_CRITERION) === false
            && $this->request->getParam('product_list_order') !== 'relevance'
        ) {
            $defaultCriterion = $this->criteriaManagenentService->getDefaultCriterion();
            if ($defaultCriterion) {
                $this->setCriterion($collection, $defaultCriterion);
            }
        }

        $select = $collection->getSelect();

        $memorizeOrders = $select->getPart(\Magento\Framework\DB\Select::ORDER);

        $select->reset(\Magento\Framework\DB\Select::ORDER);

        $dir = $collection->getFlag(self::FLAG_DIRECTION);
        $dir = $dir ? $dir : null;

        $this->collectionService->joinSortingIndex($select);
        #global factors
        $globalExpressions = [];
        foreach ($this->getGlobalFactors() as $factor) {
            $globalExpressions[] = $this->quoteFormula(Indexer::getScoreColumn($factor), $factor->getWeight());
        }
        $this->collectionService->addOrder($select, $globalExpressions, 'desc');

        $criterion = $this->getCurrentCriterion();
        if ($criterion) {
            #criterion factors
            foreach ($criterion->getConditionCluster()->getFrames() as $idx => $frame) {
                $frameExpressions = [];

                if ($dir === null) {
                    $dir = $frame->getDirection();
                }

                $dir = $idx === 0 ? $dir : $frame->getDirection();

                foreach ($frame->getNodes() as $node) {
                    if ($node->getSortBy() == CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE) {
                        $attributeExpr = $this->collectionService->joinAttribute($select, $node->getAttribute());

                        $frameExpressions[] = $attributeExpr;
                    } else {
                        $frameExpressions[] = $this->quoteFormula(Indexer::getScoreColumnById($node->getRankingFactor()), $node->getWeight());
                    }
                }

                $this->collectionService->addOrder($select, $frameExpressions, $dir);
            }
        }

        foreach ($memorizeOrders as $order) {
            $this->collectionService->addOrder($select, [$order], null);
        }

        if ($this->configProvider->isDebug()) {
            DebugService::logCollection($collection);
            DebugService::setCurrentCriterion($this->getCurrentCriterion());
        }

        return $collection;
    }

    /** @return RankingFactorInterface[] */
    private function getGlobalFactors(): array
    {
        $rankingFactors = $this->rankingFactorRepository->getCollection();
        $rankingFactors->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(RankingFactorInterface::IS_GLOBAL, 1);

        return $rankingFactors->getItems();
    }

    public function getDefaultCriterion(): ?CriterionInterface
    {
        $criterion = $this->criterionRepository->getCollection()
            ->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1)
            ->setOrder(CriterionInterface::IS_DEFAULT, 'desc')
            ->setOrder(CriterionInterface::POSITION, 'asc')
            ->getFirstItem();

        return $criterion->getId() ? $criterion : null;
    }

    private function quoteFormula(string $columnName, int $weight): string
    {
        $storeId   = (int)$this->storeManager->getStore()->getId();
        return 'IFNULL(mst_sorting_index_'. $storeId .'.' . $columnName . ', IFNULL(mst_sorting_index_0.' . $columnName . ', 0)) * ' . $weight;
    }
}
