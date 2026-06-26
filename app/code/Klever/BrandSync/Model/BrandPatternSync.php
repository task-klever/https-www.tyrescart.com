<?php
declare(strict_types=1);

namespace Klever\BrandSync\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class BrandPatternSync
{
    private ResourceConnection $resource;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Run full sync: brands + patterns + meta
     * Returns summary array with counts
     */
    public function execute(): array
    {
        $summary = [
            'brands_created' => 0,
            'brands_skipped' => 0,
            'patterns_created' => 0,
            'patterns_skipped' => 0,
            'meta_updated' => 0,
            'tabs_updated' => 0,
            'pattern_meta_updated' => 0,
            'errors' => [],
        ];

        try {
            $brandResult = $this->syncBrands();
            $summary['brands_created'] = $brandResult['created'];
            $summary['brands_skipped'] = $brandResult['skipped'];

            $patternResult = $this->syncPatterns();
            $summary['patterns_created'] = $patternResult['created'];
            $summary['patterns_skipped'] = $patternResult['skipped'];

            $metaResult = $this->syncMeta();
            $summary['meta_updated'] = $metaResult['updated'];

            $tabsResult = $this->syncTabTitles();
            $summary['tabs_updated'] = $tabsResult['updated'];

            $patternMetaResult = $this->syncPatternMeta();
            $summary['pattern_meta_updated'] = $patternMetaResult['updated'];
        } catch (\Exception $e) {
            $summary['errors'][] = $e->getMessage();
            $this->logger->error('BrandSync error: ' . $e->getMessage());
        }

        return $summary;
    }

    /**
     * Sync brands: create mgs_brand entries for any brand attribute options
     * that don't have a corresponding mgs_brand row yet
     */
    private function syncBrands(): array
    {
        $conn = $this->resource->getConnection();
        $created = 0;
        $skipped = 0;

        // Get brand attribute ID
        $brandAttrId = $conn->fetchOne(
            'SELECT attribute_id FROM eav_attribute WHERE attribute_code = "brand" AND entity_type_id = 4'
        );
        if (!$brandAttrId) {
            throw new \RuntimeException('Brand attribute not found');
        }

        // Find brand options used on products but missing from mgs_brand
        $missingBrands = $conn->fetchAll('
            SELECT DISTINCT eao.option_id, eaov.value as brand_name
            FROM eav_attribute_option eao
            JOIN eav_attribute_option_value eaov ON eaov.option_id = eao.option_id AND eaov.store_id = 0
            JOIN catalog_product_entity_int cpei ON cpei.value = eao.option_id
            JOIN eav_attribute ea ON ea.attribute_id = cpei.attribute_id AND ea.attribute_code = "brand"
            LEFT JOIN mgs_brand mb ON mb.option_id = eao.option_id
            WHERE eao.attribute_id = ? AND mb.brand_id IS NULL
            ORDER BY eaov.value
        ', [$brandAttrId]);

        if (empty($missingBrands)) {
            return ['created' => 0, 'skipped' => 0];
        }

        // Get all store IDs for mgs_brand_store
        $storeIds = [];
        foreach ($this->storeManager->getStores(true) as $store) {
            $storeIds[] = (int)$store->getId();
        }

        foreach ($missingBrands as $brand) {
            $brandName = $brand['brand_name'];
            $optionId = $brand['option_id'];
            $urlKey = $this->generateUrlKey($brandName);

            // Double-check not exists (by url_key or option_id)
            $exists = $conn->fetchOne(
                'SELECT brand_id FROM mgs_brand WHERE option_id = ? OR url_key = ?',
                [$optionId, $urlKey]
            );
            if ($exists) {
                $skipped++;
                continue;
            }

            // Get product image from first product of this brand (for small_image)
            $productImage = $conn->fetchOne('
                SELECT v.value FROM catalog_product_entity_varchar v
                JOIN eav_attribute a ON a.attribute_id = v.attribute_id AND a.attribute_code = "image" AND a.entity_type_id = 4
                JOIN catalog_product_entity_int cpei ON cpei.entity_id = v.entity_id
                JOIN eav_attribute ea ON ea.attribute_id = cpei.attribute_id AND ea.attribute_code = "brand"
                WHERE cpei.value = ? AND v.value IS NOT NULL AND v.value != "no_selection"
                LIMIT 1
            ', [$optionId]);

            // Detect brand_category from product parts_category
            $partsCategory = $conn->fetchOne('
                SELECT eaov.value FROM catalog_product_entity_int cpei
                JOIN eav_attribute ea ON ea.attribute_id = cpei.attribute_id AND ea.attribute_code = "parts_category"
                JOIN eav_attribute_option_value eaov ON eaov.option_id = cpei.value AND eaov.store_id = 0
                JOIN catalog_product_entity_int cpei2 ON cpei2.entity_id = cpei.entity_id
                JOIN eav_attribute ea2 ON ea2.attribute_id = cpei2.attribute_id AND ea2.attribute_code = "brand"
                WHERE cpei2.value = ?
                LIMIT 1
            ', [$optionId]);

            $conn->insert('mgs_brand', [
                'name' => $brandName,
                'url_key' => $urlKey,
                'small_image' => $productImage ?: null,
                'image' => $productImage ?: null,
                'status' => 1,
                'is_featured' => 0,
                'sort_order' => 99,
                'option_id' => $optionId,
                'brand_category' => $partsCategory ?: 'Tyres',
            ]);

            $brandId = $conn->lastInsertId('mgs_brand');

            // Add store associations
            foreach ($storeIds as $storeId) {
                $conn->insert('mgs_brand_store', [
                    'brand_id' => $brandId,
                    'store_id' => $storeId,
                ]);
            }

            $created++;
            $this->logger->info("BrandSync: Created brand '{$brandName}' (option_id: {$optionId})");
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Sync patterns: create mgs_brand_patternmanagement entries for any
     * brand+pattern combos found in products but missing from the table
     */
    private function syncPatterns(): array
    {
        $conn = $this->resource->getConnection();
        $created = 0;
        $skipped = 0;

        // Find all brand+pattern combos from products not in pattern management
        $missingPatterns = $conn->fetchAll('
            SELECT DISTINCT
                b.name as brand_name,
                eaov.value as pattern_name,
                MIN(cpei.entity_id) as sample_entity_id
            FROM catalog_product_entity_int cpei
            JOIN eav_attribute ea ON ea.attribute_id = cpei.attribute_id AND ea.attribute_code = "pattern"
            JOIN eav_attribute_option_value eaov ON eaov.option_id = cpei.value AND eaov.store_id = 0
            JOIN catalog_product_entity_int cpei2 ON cpei2.entity_id = cpei.entity_id
            JOIN eav_attribute ea2 ON ea2.attribute_id = cpei2.attribute_id AND ea2.attribute_code = "brand"
            JOIN mgs_brand b ON b.option_id = cpei2.value
            LEFT JOIN mgs_brand_patternmanagement pm
                ON pm.brand = b.name AND pm.pattern = eaov.value
            WHERE pm.pattern_id IS NULL
            GROUP BY b.name, eaov.value
            ORDER BY b.name, eaov.value
        ');

        if (empty($missingPatterns)) {
            return ['created' => 0, 'skipped' => 0];
        }

        foreach ($missingPatterns as $row) {
            $brandName = $row['brand_name'];
            $patternName = $row['pattern_name'];
            $entityId = $row['sample_entity_id'];
            $urlKey = $this->generateUrlKey($patternName);

            // Double-check not exists
            $exists = $conn->fetchOne(
                'SELECT pattern_id FROM mgs_brand_patternmanagement WHERE brand = ? AND pattern = ?',
                [$brandName, $patternName]
            );
            if ($exists) {
                $skipped++;
                continue;
            }

            // Get brand option_id (used as brand_id in pattern table)
            $brandOptionId = $conn->fetchOne(
                'SELECT option_id FROM mgs_brand WHERE name = ?',
                [$brandName]
            );

            // Get pattern attribute option_id (used as pattern_id in pattern table)
            $patternOptionId = $conn->fetchOne('
                SELECT eao.option_id FROM eav_attribute_option eao
                JOIN eav_attribute_option_value eaov ON eaov.option_id = eao.option_id AND eaov.store_id = 0
                JOIN eav_attribute ea ON ea.attribute_id = eao.attribute_id AND ea.attribute_code = "pattern"
                WHERE eaov.value = ?
            ', [$patternName]);

            // Get product image from the sample product
            $productImage = $conn->fetchOne('
                SELECT v.value FROM catalog_product_entity_varchar v
                JOIN eav_attribute a ON a.attribute_id = v.attribute_id AND a.attribute_code = "image" AND a.entity_type_id = 4
                WHERE v.entity_id = ? AND v.value IS NOT NULL AND v.value != "no_selection"
                LIMIT 1
            ', [$entityId]);

            // Convert product image path to pattern image path
            // Product images in DB: /tyres/wanli-sa302.jpg
            // Files live at: pub/media/catalog/product/tyres/wanli-sa302.jpg
            // Pattern images render as: $mediaUrl . $pattern->getImage()
            // So store: catalog/product/tyres/wanli-sa302.jpg
            $patternImage = $productImage ? 'catalog/product' . $productImage : null;

            $conn->insert('mgs_brand_patternmanagement', [
                'brand_id' => (int)($brandOptionId ?: 0),
                'brand' => $brandName,
                'pattern_id' => (int)($patternOptionId ?: 0),
                'pattern' => $patternName,
                'url_key' => $urlKey,
                'image' => $patternImage,
                'short_description' => null,
                'status' => 1,
            ]);

            $created++;
        }

        $this->logger->info("BrandSync: Created {$created} pattern entries, skipped {$skipped}");
        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Sync meta: fill missing meta_keywords (title) and meta_description
     * Pattern: "Buy {Brand} Tyres Online in Dubai, UAE | TyresCart"
     */
    private function syncMeta(): array
    {
        $conn = $this->resource->getConnection();
        $updated = 0;

        $brands = $conn->fetchAll(
            'SELECT brand_id, name, brand_category FROM mgs_brand WHERE status = 1 AND (meta_keywords IS NULL OR meta_keywords = "")'
        );

        foreach ($brands as $b) {
            $name = $b['name'];
            $cat = $b['brand_category'] ?: 'Tyres';

            switch ($cat) {
                case 'Battery':
                    $productType = 'Batteries';
                    $productTypeLower = 'batteries';
                    $offerWord = 'battery';
                    break;
                case 'Motorcycle Tyres':
                    $productType = 'Motorcycle Tyres';
                    $productTypeLower = 'motorcycle tyres';
                    $offerWord = 'motorcycle tyre';
                    break;
                case 'Rim Protectors':
                    $productType = 'Rim Protectors';
                    $productTypeLower = 'rim protectors';
                    $offerWord = 'rim protector';
                    break;
                default:
                    $productType = 'Tyres';
                    $productTypeLower = 'tyres';
                    $offerWord = 'tyre';
                    break;
            }

            $metaTitle = "Buy {$name} {$productType} Online in Dubai, UAE | TyresCart";
            $metaDesc = "Shop premium {$name} {$productTypeLower} online at TyresCart in Dubai and across the UAE. Enjoy great {$offerWord} offers, expert fitment, and reliable car services at our trusted auto care centers.";

            $conn->update('mgs_brand', [
                'meta_keywords' => $metaTitle,
                'meta_description' => $metaDesc,
            ], ['brand_id = ?' => $b['brand_id']]);

            $updated++;
        }

        if ($updated > 0) {
            $this->logger->info("BrandSync: Updated meta for {$updated} brands");
        }
        return ['updated' => $updated];
    }

    /**
     * Sync tab titles: fill missing tab1_title and tab2_title
     * Pattern: "About {Brand} Tyres" / "Shop {Brand} Tyres In UAE"
     */
    private function syncTabTitles(): array
    {
        $conn = $this->resource->getConnection();
        $updated = 0;

        $brands = $conn->fetchAll(
            'SELECT brand_id, name, brand_category FROM mgs_brand WHERE status = 1 AND (tab1_title IS NULL OR tab1_title = "")'
        );

        foreach ($brands as $b) {
            $name = $b['name'];
            $cat = $b['brand_category'] ?: 'Tyres';

            switch ($cat) {
                case 'Battery':
                    $productType = 'Batteries';
                    break;
                case 'Motorcycle Tyres':
                    $productType = 'Motorcycle Tyres';
                    break;
                case 'Rim Protectors':
                    $productType = 'Rim Protectors';
                    break;
                default:
                    $productType = 'Tyres';
                    break;
            }

            $conn->update('mgs_brand', [
                'tab1_title' => "About {$name} {$productType}",
                'tab2_title' => "Shop {$name} {$productType} In UAE",
            ], ['brand_id = ?' => $b['brand_id']]);

            $updated++;
        }

        if ($updated > 0) {
            $this->logger->info("BrandSync: Updated tab titles for {$updated} brands");
        }
        return ['updated' => $updated];
    }

    /**
     * Sync pattern meta: fill missing meta_title and meta_description for patterns
     * Pattern: "Buy {Brand} {Pattern} Tyres Online in UAE | TyresCart"
     */
    private function syncPatternMeta(): array
    {
        $conn = $this->resource->getConnection();
        $updated = 0;

        $patterns = $conn->fetchAll(
            'SELECT pm.patternmanagement_id, pm.brand, pm.pattern, b.brand_category
             FROM mgs_brand_patternmanagement pm
             LEFT JOIN mgs_brand b ON b.name = pm.brand
             WHERE pm.status = 1 AND (pm.meta_title IS NULL OR pm.meta_title = "")'
        );

        foreach ($patterns as $p) {
            $brand = $p['brand'];
            $pattern = $p['pattern'];
            $cat = $p['brand_category'] ?: 'Tyres';

            switch ($cat) {
                case 'Battery':
                    $productType = 'Batteries';
                    $productTypeLower = 'batteries';
                    break;
                case 'Motorcycle Tyres':
                    $productType = 'Motorcycle Tyres';
                    $productTypeLower = 'motorcycle tyres';
                    break;
                default:
                    $productType = 'Tyres';
                    $productTypeLower = 'tyres';
                    break;
            }

            $metaTitle = "Buy {$brand} {$pattern} {$productType} Online in UAE | TyresCart";
            $metaDesc = "Shop {$brand} {$pattern} {$productTypeLower} in Dubai, Abu Dhabi, and UAE. Best prices, expert fitment, and fast delivery at TyresCart.";

            $conn->update('mgs_brand_patternmanagement', [
                'meta_title' => $metaTitle,
                'meta_description' => $metaDesc,
            ], ['patternmanagement_id = ?' => $p['patternmanagement_id']]);

            $updated++;
        }

        if ($updated > 0) {
            $this->logger->info("BrandSync: Updated meta for {$updated} patterns");
        }
        return ['updated' => $updated];
    }

    /**
     * Generate URL-safe key from name
     */
    private function generateUrlKey(string $name): string
    {
        $urlKey = strtolower(trim($name));
        $urlKey = preg_replace('/[^a-z0-9\s-]/', '', $urlKey);
        $urlKey = preg_replace('/[\s-]+/', '-', $urlKey);
        return trim($urlKey, '-');
    }
}
