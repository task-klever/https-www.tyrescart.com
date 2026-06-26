<?php

declare(strict_types=1);

namespace Klever\ElasticTyreSearch\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;

class ProductStatsProvider
{
    private const CACHE_TAG = 'klever_tyre_enrich';
    private const CACHE_TTL = 86400;

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly CacheInterface     $cache,
        private readonly EavConfig          $eavConfig
    ) {
    }

    /**
     * Get product statistics for a tyre size label (e.g. "205/55 R16")
     */
    public function getStatsForTyreSize(string $tyreSizeLabel): array
    {
        $cacheKey = self::CACHE_TAG . '_pstats_' . md5($tyreSizeLabel);
        $raw = $this->cache->load($cacheKey);
        if ($raw) {
            return json_decode($raw, true);
        }

        $conn = $this->resource->getConnection();

        // Resolve tyre_size attribute + option ID
        $tyreSizeAttr = $this->eavConfig->getAttribute('catalog_product', 'tyre_size');
        if (!$tyreSizeAttr || !$tyreSizeAttr->getId()) {
            return $this->emptyResult();
        }
        $tyreSizeAttrId = (int) $tyreSizeAttr->getId();

        $optionId = (int) $conn->fetchOne(
            "SELECT eaov.option_id
             FROM eav_attribute_option_value eaov
             INNER JOIN eav_attribute_option eao ON eao.option_id = eaov.option_id
             WHERE eao.attribute_id = :attr_id AND eaov.value = :label AND eaov.store_id = 0
             LIMIT 1",
            [':attr_id' => $tyreSizeAttrId, ':label' => $tyreSizeLabel]
        );

        if (!$optionId) {
            $result = $this->emptyResult();
            $this->cache->save(json_encode($result), $cacheKey, [self::CACHE_TAG], self::CACHE_TTL);
            return $result;
        }

        // Get product IDs matching this tyre size (enabled only)
        $tyreSizeTable = $tyreSizeAttr->getBackendTable();
        $statusAttr = $this->eavConfig->getAttribute('catalog_product', 'status');
        $statusAttrId = (int) $statusAttr->getId();
        $statusTable = $statusAttr->getBackendTable();

        $productIds = $conn->fetchCol(
            "SELECT ts.entity_id FROM {$tyreSizeTable} ts
             INNER JOIN {$statusTable} st
                ON st.entity_id = ts.entity_id AND st.attribute_id = :status_id AND st.store_id = 0 AND st.value = 1
             WHERE ts.attribute_id = :attr_id AND ts.value = :opt_id",
            [':attr_id' => $tyreSizeAttrId, ':opt_id' => $optionId, ':status_id' => $statusAttrId]
        );

        if (empty($productIds)) {
            $result = $this->emptyResult();
            $this->cache->save(json_encode($result), $cacheKey, [self::CACHE_TAG], self::CACHE_TTL);
            return $result;
        }

        $idList = implode(',', array_map('intval', $productIds));

        // Price stats
        $priceAttr = $this->eavConfig->getAttribute('catalog_product', 'price');
        $priceAttrId = (int) $priceAttr->getId();
        $priceTable = $priceAttr->getBackendTable();

        $priceStats = $conn->fetchRow(
            "SELECT MIN(value) AS price_min, MAX(value) AS price_max
             FROM {$priceTable}
             WHERE attribute_id = :attr_id AND entity_id IN ({$idList}) AND value > 0",
            [':attr_id' => $priceAttrId]
        );

        // Cheapest product
        $nameAttr = $this->eavConfig->getAttribute('catalog_product', 'name');
        $nameAttrId = (int) $nameAttr->getId();
        $nameTable = $nameAttr->getBackendTable();

        $urlKeyAttr = $this->eavConfig->getAttribute('catalog_product', 'url_key');
        $urlKeyAttrId = (int) $urlKeyAttr->getId();
        $urlKeyTable = $urlKeyAttr->getBackendTable();

        $cheapest = $conn->fetchRow(
            "SELECT n.value AS name, p.value AS price, u.value AS url_key
             FROM {$priceTable} p
             INNER JOIN {$nameTable} n ON n.entity_id = p.entity_id AND n.attribute_id = :name_id AND n.store_id = 0
             LEFT JOIN {$urlKeyTable} u ON u.entity_id = p.entity_id AND u.attribute_id = :url_id AND u.store_id = 0
             WHERE p.attribute_id = :price_id AND p.entity_id IN ({$idList}) AND p.value > 0
             ORDER BY p.value ASC LIMIT 1",
            [':price_id' => $priceAttrId, ':name_id' => $nameAttrId, ':url_id' => $urlKeyAttrId]
        );

        // Premium product
        $premium = $conn->fetchRow(
            "SELECT n.value AS name, p.value AS price, u.value AS url_key
             FROM {$priceTable} p
             INNER JOIN {$nameTable} n ON n.entity_id = p.entity_id AND n.attribute_id = :name_id AND n.store_id = 0
             LEFT JOIN {$urlKeyTable} u ON u.entity_id = p.entity_id AND u.attribute_id = :url_id AND u.store_id = 0
             WHERE p.attribute_id = :price_id AND p.entity_id IN ({$idList}) AND p.value > 0
             ORDER BY p.value DESC LIMIT 1",
            [':price_id' => $priceAttrId, ':name_id' => $nameAttrId, ':url_id' => $urlKeyAttrId]
        );

        // Brand stats
        $brandAttr = $this->eavConfig->getAttribute('catalog_product', 'brand');
        $brandCount = 0;
        $brandNames = [];

        if ($brandAttr && $brandAttr->getId()) {
            $brandAttrId = (int) $brandAttr->getId();
            $brandTable = $brandAttr->getBackendTable();

            /* $brandRows = $conn->fetchAll(
                "SELECT eaov.value AS brand_name, COUNT(*) AS cnt
                 FROM {$brandTable} b
                 INNER JOIN eav_attribute_option_value eaov
                    ON eaov.option_id = b.value AND eaov.store_id = 0
                 WHERE b.attribute_id = :attr_id AND b.entity_id IN ({$idList})
                 GROUP BY eaov.value
                 ORDER BY cnt DESC
                 LIMIT 6",
                [':attr_id' => $brandAttrId]
            ); */

            $brandRows = $conn->fetchAll(
                "SELECT eaov.value AS brand_name, mb.small_image AS brand_image, COUNT(*) AS cnt
                 FROM {$brandTable} b
                 INNER JOIN eav_attribute_option_value eaov
                    ON eaov.option_id = b.value AND eaov.store_id = 0
                 LEFT JOIN mgs_brand mb
                    ON LOWER(mb.name) = LOWER(eaov.value) AND mb.status = 1
                 WHERE b.attribute_id = :attr_id AND b.entity_id IN ({$idList})
                 GROUP BY eaov.value, mb.small_image
                 ORDER BY cnt DESC",
                [':attr_id' => $brandAttrId]
            );

            $brandCount = (int) $conn->fetchOne(
                "SELECT COUNT(DISTINCT value) FROM {$brandTable}
                 WHERE attribute_id = :attr_id AND entity_id IN ({$idList})",
                [':attr_id' => $brandAttrId]
            );

            foreach ($brandRows as $row) {
                $brandNames[] = [
                    'name'  => $row['brand_name'],
                    'image' => $row['brand_image'] ?? '',
                ];
            }
        }

        $result = [
            'product_count' => count($productIds),
            'brand_count'   => $brandCount,
            'brand_names'   => $brandNames,
            'price_min'     => $priceStats['price_min'] ? (float) $priceStats['price_min'] : null,
            'price_max'     => $priceStats['price_max'] ? (float) $priceStats['price_max'] : null,
            'cheapest'      => $cheapest ? ['name' => $cheapest['name'], 'price' => (float) $cheapest['price'], 'url_key' => $cheapest['url_key'] ?? ''] : null,
            'premium'       => $premium ? ['name' => $premium['name'], 'price' => (float) $premium['price'], 'url_key' => $premium['url_key'] ?? ''] : null,
        ];

        $this->cache->save(json_encode($result), $cacheKey, [self::CACHE_TAG], self::CACHE_TTL);
        return $result;
    }

    private function emptyResult(): array
    {
        return [
            'product_count' => 0,
            'brand_count'   => 0,
            'brand_names'   => [],
            'price_min'     => null,
            'price_max'     => null,
            'cheapest'      => null,
            'premium'       => null,
        ];
    }
}
