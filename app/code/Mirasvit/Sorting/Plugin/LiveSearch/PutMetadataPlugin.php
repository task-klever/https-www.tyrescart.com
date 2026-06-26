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


namespace Mirasvit\Sorting\Plugin\LiveSearch;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\DataExporter\Model\Indexer\FeedIndexMetadata;
use Magento\DataExporter\Model\Indexer\FeedIndexProcessorCreateUpdate;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Block\DebugInPage;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Mirasvit\Sorting\Service\CriteriaApplierService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PutMetadataPlugin
{
    private $rankingFactrorRepository;

    private $criterionRepository;

    private $resource;

    private $productCollectionFactory;

    private $criteriaApplierService;

    private $storeManager;

    private $debugInPage;

    public function __construct(
        RankingFactorRepository $rankingFactorRepository,
        CriterionRepository $criterionRepository,
        ResourceConnection $resource,
        ProductCollectionFactory $productCollectionFactory,
        CriteriaApplierService $criteriaApplierService,
        StoreManagerInterface $storeManager,
        DebugInPage $debugInPage
    ) {
        $this->rankingFactrorRepository = $rankingFactorRepository;
        $this->criterionRepository      = $criterionRepository;
        $this->resource                 = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->criteriaApplierService   = $criteriaApplierService;
        $this->storeManager             = $storeManager;
        $this->debugInPage              = $debugInPage;
    }

    /**
     * @param FeedIndexProcessorCreateUpdate $subject
     * @param mixed $output
     * @param FeedIndexMetadata $metadata
     * @return mixed
     */
    public function afterFullReindex(FeedIndexProcessorCreateUpdate $subject, $output, FeedIndexMetadata $metadata)
    {
        switch($metadata->getFeedName()) {
            case 'productAttributes':
                $this->addFactorsAndCriteriaAsAttributeToMetadata($metadata);

                break;
            case 'products':
                foreach ($this->storeManager->getStores() as $store) {
                    if ($store->getId() == 0) {
                        continue;
                    }

                    $this->addScoresToProductMetadata($metadata, $store);
                }

                break;
        }

        return $output;
    }

    private function addFactorsAndCriteriaAsAttributeToMetadata(FeedIndexMetadata $metadata): void
    {
        $query = "SELECT id FROM " . $this->resource->getTableName($metadata->getFeedTableName()) . " ORDER BY id DESC LIMIT 1";

        $lastId = $this->resource->getConnection()
            ->query($query)
            ->fetchAll()[0]['id'];

        $newId = (int)$lastId + 10;

        $stores = $this->storeManager->getStores();

        // add glodal sorting attribute per store
        foreach ($stores as $store) {
            if ($store->getId() == 0) {
                continue;
            }

            $this->addPseudoAttributeMetadata(
                $metadata->getFeedTableName(),
                (string)$newId,
                "sorting_global",
                "Sorting Global",
                $this->storeManager->getGroup($store->getStoreGroupId())->getCode(),
                $this->storeManager->getWebsite($store->getWebsiteId())->getCode(),
                $store->getCode()
            );
        }

        $newId++;

        $criterias = $this->criterionRepository->getCollection()
            ->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1);

        // add criterion attribute by store
        foreach ($criterias as $criterion) {
            foreach ($stores as $store) {
                if ($store->getId() == 0) {
                    continue;
                }

                $this->addPseudoAttributeMetadata(
                    $metadata->getFeedTableName(),
                    (string)$newId,
                    $criterion->getCode(),
                    $criterion->getName(),
                    $this->storeManager->getGroup($store->getStoreGroupId())->getCode(),
                    $this->storeManager->getWebsite($store->getWebsiteId())->getCode(),
                    $store->getCode()
                );
            }

            $newId++;
        }
    }

    private function addPseudoAttributeMetadata(
        string $tableName,
        string $id,
        string $code,
        string $label,
        string $storeCode,
        string $websiteCode,
        string $storeViewCode
    ): void {
        $keys = ['id', 'store_view_code', 'feed_data', 'is_deleted'];

        $feedData = [
            "id"                   => $id,
            "storeCode"            => $storeCode,
            "websiteCode"          => $websiteCode,
            "storeViewCode"        => $storeViewCode,
            "attributeCode"        => $code,
            "attributeType"        => "catalog_product",
            "dataType"             => "varchar",
            "multi"                => false,
            "label"                => $label,
            "frontendInput"        => "text",
            "required"             => false,
            "unique"               => false,
            "global"               => false,
            "visible"              => true,
            "searchable"           => false,
            "filterable"           => false,
            "visibleInCompareList" => false,
            "visibleInListing"     => false,
            "sortable"             => true,
            "visibleInSearch"      => false,
            "filterableInSearch"   => false,
            "searchWeight"         => 1,
            "usedForRules"         => false,
            "boolean"              => false,
            "systemAttribute"      => true,
            "numeric"              => true,
            "attributeOptions"     => null
        ];

        $this->resource->getConnection()->insertOnDuplicate(
            $this->resource->getTableName($tableName),
            [
                'id'              => $id,
                'store_view_code' => $storeViewCode,
                'feed_data'       => SerializeService::encode($feedData),
                'is_deleted'      => 0
            ],
            $keys
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function addScoresToProductMetadata(FeedIndexMetadata $metadata, StoreInterface $store): void
    {
        $selectQuery = "SELECT sku FROM "
            . $this->resource->getTableName($metadata->getFeedTableName())
            . " WHERE store_view_code = '" . $store->getCode() . "'";
        $productsMetadata = $this->resource->getConnection()->query($selectQuery);

        $skus = [];

        while ($row = $productsMetadata->fetch()) {
            $skus[] = $row['sku'];
        }

        $productCollection = $this->productCollectionFactory->create()
            ->addFieldToFilter('sku', ['in' => $skus])
            ->setStore($store);

        $productCollection->addFieldToSelect('entity_id')->addFieldToSelect('sku');

        $rows = [];

        // add global scores to products metadata
        foreach ($productCollection as $product) {
            if (!in_array($product->getSku(), $skus)) {
                continue;
            }

            $rows[$product->getSku()] = [];
            $feedData                 = ['attributes' => []];

            $feedData['attributes'][] = [
                "attributeCode" => "sorting_global",
                "type"          => "text",
                "value"         => [$this->getGlobalScoreByProduct($product, (int)$store->getId())],
                "valueId"       => null
            ];

            $rows[$product->getSku()]['feed_data'] = $feedData;

            if (count($rows) >= 1000) {
                $this->updateBatch($rows, $store, $metadata->getFeedTableName());
                $rows = [];
            }
        }

        if (count($rows)) {
            $this->updateBatch($rows, $store, $metadata->getFeedTableName());
            $rows = [];
        }

        $criterias = $this->criterionRepository->getCollection()->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1);

        // add criterias scores to products metadata
        foreach ($criterias as $criterion) {
            $collection = $this->productCollectionFactory->create()
                ->addFieldToFilter('sku', ['in' => $skus])
                ->setStore($store);

            $this->criteriaApplierService->setCriterion($collection, $criterion);
            $collection = $this->criteriaApplierService->sortCollection($collection);

            $pos = 1;

            $rows = [];

            foreach ($collection as $product) {
                if (!in_array($product->getSku(), $skus)) {
                    continue;
                }

                $rows[$product->getSku()] = [];
                $feedData                 = ['attributes' => []];

                $feedData['attributes'][] = [
                    "attributeCode" => $criterion->getCode(),
                    "type"          => "text",
                    "value"         => [$pos],
                    "valueId"       => null
                ];

                $rows[$product->getSku()]['feed_data'] = $feedData;

                $pos++;

                if (count($rows) >= 1000) {
                    $this->updateBatch($rows, $store, $metadata->getFeedTableName());
                    $rows = [];
                }
            }

            if (count($rows)) {
                $this->updateBatch($rows, $store, $metadata->getFeedTableName());
                $rows = [];
            }
        }
    }

    private function updateBatch(array $rows, StoreInterface $store, string $tableName)
    {
        $skus = array_map(function ($sku) {
            return "'" . $sku . "'";
        }, array_keys($rows));

        $selectQuery = "SELECT * FROM "
            . $this->resource->getTableName($tableName)
            . " WHERE store_view_code = '" . $store->getCode() . "' AND sku IN (" . implode(',', $skus) . ")";
        $productsMetadata = $this->resource->getConnection()->query($selectQuery);

        $keys = null;

        while ($feed = $productsMetadata->fetch()) {
            if (!$keys) {
                $keys = array_keys($feed);
            }

            $feed['feed_data'] = SerializeService::decode($feed['feed_data']);

            $additionalData = isset($rows[$feed['sku']])
                ? $rows[$feed['sku']]['feed_data']['attributes']
                : [];

            if (count($additionalData)) {
                foreach ($additionalData as $scoreData) {
                    $feed['feed_data']['attributes'][] = $scoreData;
                }

                $feed['feed_data'] = SerializeService::encode($feed['feed_data']);

                $this->resource->getConnection()->insertOnDuplicate(
                    $this->resource->getTableName($tableName),
                    $feed,
                    $keys
                );
            }
        }

    }

    private function getGlobalScoreByProduct(ProductInterface $product, int $storeId): float
    {
        $productScores = $this->debugInPage->getScoresByProduct($product, $storeId);

        $totalScore = 0;

        foreach ($productScores['global'] as $scoreData) {
            $totalScore += $scoreData['score'] * $scoreData['weight'];
        }

        return $totalScore;
    }
}
