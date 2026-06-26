<?php

namespace Klever\Sitemap\Model;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SitemapSplitter
{
    private DirectoryList $directoryList;
    private LoggerInterface $logger;
    private CmsPageCollectionFactory $cmsPageCollectionFactory;
    private ResourceConnection $resourceConnection;
    private StoreManagerInterface $storeManager;

    /**
     * @var array|null Cached CMS slugs loaded from database
     */
    private ?array $cmsSlugs = null;

    /**
     * @var array|null Cached: category_id => parent_id
     */
    private ?array $categoryParentMap = null;

    /**
     * @var array|null Cached: category_id => level
     */
    private ?array $categoryLevelMap = null;

    /**
     * @var array|null Dynamically loaded group category IDs (level 2 + level 3)
     */
    private ?array $groupCategoryIds = null;

    /**
     * @var array|null Cached: category url_key => category_id for fallback matching
     */
    private ?array $categoryUrlKeyMap = null;

    public function __construct(
        DirectoryList $directoryList,
        LoggerInterface $logger,
        CmsPageCollectionFactory $cmsPageCollectionFactory,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * Split the sitemap.xml into category-wise files and create an index
     *
     * @return array Summary of split results
     */
    public function execute(): array
    {
        $pubDir = $this->directoryList->getPath('pub');
        $sourceFile = $pubDir . '/sitemap.xml';
        $backupFile = $pubDir . '/sitemap-original.xml';

        // Find the right source: need a file that contains <url> blocks, not a <sitemapindex>
        $sourceFile = $this->findSourceSitemap($pubDir, $sourceFile, $backupFile);

        if (!$sourceFile) {
            throw new \RuntimeException('No valid source sitemap with <url> blocks found in: ' . $pubDir);
        }

        $this->logger->info('Klever_Sitemap: Starting category-wise sitemap split from: ' . basename($sourceFile));

        // Parse the source sitemap
        $xml = file_get_contents($sourceFile);

        // Extract all <url>...</url> blocks
        preg_match_all('/<url>.*?<\/url>/s', $xml, $matches);
        $urlBlocks = $matches[0] ?? [];

        if (empty($urlBlocks)) {
            throw new \RuntimeException('No URLs found in ' . basename($sourceFile));
        }

        // Extract namespace declarations from original sitemap
        preg_match('/<urlset([^>]*)>/', $xml, $nsMatch);
        $urlsetAttrs = $nsMatch[1] ?? ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

        // Build all mappings dynamically from database
        $this->buildCategoryTree();
        $urlToProductId = $this->getUrlToProductMap();
        $productToGroupCategory = $this->getProductToGroupCategoryMap();
        $categoryNames = $this->getCategoryNames();
        $urlToCategoryId = $this->getUrlToCategoryMap();

        $this->logger->info(sprintf(
            'Klever_Sitemap: Detected %d group categories dynamically',
            count($this->groupCategoryIds)
        ));

        // Buckets: group_category_id => [urlBlocks], plus special buckets
        $categoryBuckets = [];
        $blogBucket = [];
        $pagesBucket = [];
        $uncategorizedBucket = [];

        foreach ($urlBlocks as $urlBlock) {
            // Extract the <loc> value
            preg_match('/<loc>(.*?)<\/loc>/', $urlBlock, $locMatch);
            $url = $locMatch[1] ?? '';

            // Check blog (matches /blog and /blog/*)
            if (preg_match('#/blog(/|$)#', $url)) {
                $blogBucket[] = $urlBlock;
                continue;
            }

            // Check if it's a CMS/static page
            if ($this->isCmsPage($url)) {
                $pagesBucket[] = $urlBlock;
                continue;
            }

            // Parse the URL path and remove store code
            $path = parse_url($url, PHP_URL_PATH);
            $path = $path ? ltrim($path, '/') : '';
            $path = preg_replace('#^[a-z]{2}/#', '', $path);

            // Try to match as a product URL
            $productId = $urlToProductId[$path] ?? null;
            if ($productId && isset($productToGroupCategory[$productId])) {
                $groupId = $productToGroupCategory[$productId];
                $categoryBuckets[$groupId][] = $urlBlock;
                continue;
            }

            // Try to match as a category URL
            $categoryId = $urlToCategoryId[$path] ?? null;
            if ($categoryId) {
                $groupId = $this->getGroupCategoryId($categoryId);
                if ($groupId) {
                    $categoryBuckets[$groupId][] = $urlBlock;
                    continue;
                }
            }

            // Fallback: try matching URL path segments against category url_keys
            $fallbackGroupId = $this->getFallbackGroupId($path);
            if ($fallbackGroupId) {
                $categoryBuckets[$fallbackGroupId][] = $urlBlock;
            } else {
                // Not a product, category, or blog — treat as a static/module page
                $pagesBucket[] = $urlBlock;
            }
        }

        // Write individual sitemap files
        $summary = [];
        $writtenFiles = [];

        // Write category-wise sitemaps (grouped by top-level category)
        foreach ($categoryBuckets as $categoryId => $urls) {
            if (empty($urls)) {
                continue;
            }

            $categoryName = $categoryNames[$categoryId] ?? 'category-' . $categoryId;
            $filename = 'sitemap-' . $this->sanitizeFilename($categoryName) . '.xml';

            $filePath = $pubDir . '/' . $filename;
            file_put_contents($filePath, $this->buildSitemapXml($urlsetAttrs, $urls));

            $writtenFiles[] = $filename;
            $summary[$filename] = count($urls);

            $this->logger->info(sprintf(
                'Klever_Sitemap: Written %s (%s) with %d URLs',
                $filename,
                $categoryName,
                count($urls)
            ));
        }

        // Write blog sitemap
        if (!empty($blogBucket)) {
            $filename = 'sitemap-blog.xml';
            $filePath = $pubDir . '/' . $filename;
            file_put_contents($filePath, $this->buildSitemapXml($urlsetAttrs, $blogBucket));
            $writtenFiles[] = $filename;
            $summary[$filename] = count($blogBucket);
            $this->logger->info(sprintf('Klever_Sitemap: Written %s with %d URLs', $filename, count($blogBucket)));
        }

        // Write CMS pages sitemap
        if (!empty($pagesBucket)) {
            $filename = 'sitemap-pages.xml';
            $filePath = $pubDir . '/' . $filename;
            file_put_contents($filePath, $this->buildSitemapXml($urlsetAttrs, $pagesBucket));
            $writtenFiles[] = $filename;
            $summary[$filename] = count($pagesBucket);
            $this->logger->info(sprintf('Klever_Sitemap: Written %s with %d URLs', $filename, count($pagesBucket)));
        }

        // Write uncategorized products sitemap
        if (!empty($uncategorizedBucket)) {
            $filename = 'sitemap-other.xml';
            $filePath = $pubDir . '/' . $filename;
            file_put_contents($filePath, $this->buildSitemapXml($urlsetAttrs, $uncategorizedBucket));
            $writtenFiles[] = $filename;
            $summary[$filename] = count($uncategorizedBucket);
            $this->logger->info(sprintf('Klever_Sitemap: Written %s with %d URLs', $filename, count($uncategorizedBucket)));
        }

        // Clean up old split files that are no longer needed
        $this->cleanupOldFiles($pubDir, $writtenFiles);

        // Create sitemap index
        $this->createSitemapIndex($pubDir, $writtenFiles);

        $summary['total'] = count($urlBlocks);
        $this->logger->info(sprintf(
            'Klever_Sitemap: Split complete. Total: %d URLs across %d files',
            count($urlBlocks),
            count($writtenFiles)
        ));

        return $summary;
    }

    /**
     * Build the full category tree from database (parent map, level map, group IDs)
     * Dynamically detects group categories at level 2 and level 3
     * Any new category created in admin will automatically be picked up
     */
    private function buildCategoryTree(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('catalog_category_entity');

        $select = $connection->select()
            ->from($table, ['entity_id', 'parent_id', 'level']);

        $rows = $connection->fetchAll($select);

        $this->categoryParentMap = [];
        $this->categoryLevelMap = [];
        $this->groupCategoryIds = [];

        // Root category ID (level 1) — default Magento root
        $rootCategoryId = null;

        foreach ($rows as $row) {
            $id = (int)$row['entity_id'];
            $parentId = (int)$row['parent_id'];
            $level = (int)$row['level'];

            $this->categoryParentMap[$id] = $parentId;
            $this->categoryLevelMap[$id] = $level;

            if ($level === 1) {
                $rootCategoryId = $id;
            }

            // Group categories = level 2 (top-level visible) + level 3 (subcategories of top-level)
            // These become individual sitemap files
            if ($level === 2 || $level === 3) {
                $this->groupCategoryIds[] = $id;
            }
        }
    }

    /**
     * Build URL path => product_id map from url_rewrite table
     */
    private function getUrlToProductMap(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('url_rewrite');

        $select = $connection->select()
            ->from($table, ['request_path', 'entity_id'])
            ->where('entity_type = ?', 'product')
            ->where('redirect_type = ?', 0);

        $rows = $connection->fetchAll($select);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['request_path']] = (int)$row['entity_id'];
        }

        $this->logger->info(sprintf('Klever_Sitemap: Loaded %d product URL rewrites', count($map)));

        return $map;
    }

    /**
     * Build URL path => category_id map from url_rewrite table
     */
    private function getUrlToCategoryMap(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('url_rewrite');

        $select = $connection->select()
            ->from($table, ['request_path', 'entity_id'])
            ->where('entity_type = ?', 'category')
            ->where('redirect_type = ?', 0);

        $rows = $connection->fetchAll($select);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['request_path']] = (int)$row['entity_id'];
        }

        return $map;
    }

    /**
     * Build product_id => group_category_id map
     * Maps each product to its top-level group category by walking up the tree
     */
    private function getProductToGroupCategoryMap(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $ccpTable = $this->resourceConnection->getTableName('catalog_category_product');
        $cceTable = $this->resourceConnection->getTableName('catalog_category_entity');

        $select = $connection->select()
            ->from(['ccp' => $ccpTable], ['product_id', 'category_id'])
            ->join(
                ['cce' => $cceTable],
                'ccp.category_id = cce.entity_id',
                ['level']
            )
            ->where('cce.level > ?', 1)
            ->order('cce.level DESC'); // Deepest first

        $rows = $connection->fetchAll($select);

        $map = [];
        foreach ($rows as $row) {
            $productId = (int)$row['product_id'];
            if (isset($map[$productId])) {
                continue;
            }

            $categoryId = (int)$row['category_id'];
            $groupId = $this->getGroupCategoryId($categoryId);
            if ($groupId) {
                $map[$productId] = $groupId;
            }
        }

        $this->logger->info(sprintf('Klever_Sitemap: Loaded %d product-to-group mappings', count($map)));

        return $map;
    }

    /**
     * Find the group category for a given category_id
     * Walks up the category tree to find the nearest ancestor in groupCategoryIds
     * Works dynamically — any new category added in admin is automatically detected
     */
    private function getGroupCategoryId(int $categoryId): ?int
    {
        if (in_array($categoryId, $this->groupCategoryIds, true)) {
            return $categoryId;
        }

        // Walk up the tree to find the nearest group ancestor
        $current = $categoryId;
        $visited = [];
        while (isset($this->categoryParentMap[$current])) {
            if (in_array($current, $visited, true)) {
                break;
            }
            $visited[] = $current;

            $parent = $this->categoryParentMap[$current];
            if (in_array($parent, $this->groupCategoryIds, true)) {
                return $parent;
            }
            $current = $parent;
        }

        return null;
    }

    /**
     * Get fallback group category ID by matching URL path segments against category URL keys
     * Dynamically built from url_rewrite — handles URLs not directly in url_rewrite
     * (e.g., /tyres/cars/honda/civic → matches "tyres/cars" → Cars category)
     */
    private function getFallbackGroupId(string $path): ?int
    {
        if ($this->categoryUrlKeyMap === null) {
            $this->buildCategoryUrlKeyMap();
        }

        // Try progressively shorter path prefixes to find a matching category
        // e.g., for "tyres/cars/honda/civic" try:
        //   "tyres/cars/honda/civic" → "tyres/cars/honda" → "tyres/cars" → "tyres"
        $segments = explode('/', trim($path, '/'));
        while (!empty($segments)) {
            $tryPath = implode('/', $segments);
            if (isset($this->categoryUrlKeyMap[$tryPath])) {
                $categoryId = $this->categoryUrlKeyMap[$tryPath];
                $groupId = $this->getGroupCategoryId($categoryId);
                if ($groupId) {
                    return $groupId;
                }
            }
            array_pop($segments);
        }

        return null;
    }

    /**
     * Build category URL path => category_id map for fallback matching
     */
    private function buildCategoryUrlKeyMap(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('url_rewrite');

        $select = $connection->select()
            ->from($table, ['request_path', 'entity_id'])
            ->where('entity_type = ?', 'category')
            ->where('redirect_type = ?', 0);

        $rows = $connection->fetchAll($select);

        $this->categoryUrlKeyMap = [];
        foreach ($rows as $row) {
            $path = rtrim($row['request_path'], '/');
            $this->categoryUrlKeyMap[$path] = (int)$row['entity_id'];
        }
    }

    /**
     * Load category names keyed by category_id
     */
    private function getCategoryNames(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $cceTable = $this->resourceConnection->getTableName('catalog_category_entity');
        $eav = $this->resourceConnection->getTableName('eav_attribute');
        $varchar = $this->resourceConnection->getTableName('catalog_category_entity_varchar');

        $nameAttrId = $connection->fetchOne(
            $connection->select()
                ->from($eav, ['attribute_id'])
                ->where('attribute_code = ?', 'name')
                ->where('entity_type_id = ?', $this->getCategoryEntityTypeId())
        );

        if (!$nameAttrId) {
            return [];
        }

        $select = $connection->select()
            ->from(['cce' => $cceTable], ['entity_id'])
            ->joinLeft(
                ['v' => $varchar],
                'cce.entity_id = v.entity_id AND v.attribute_id = ' . (int)$nameAttrId . ' AND v.store_id = 0',
                ['value']
            )
            ->where('cce.level > ?', 1);

        $rows = $connection->fetchAll($select);

        $names = [];
        foreach ($rows as $row) {
            $names[(int)$row['entity_id']] = $row['value'] ?? ('Category ' . $row['entity_id']);
        }

        return $names;
    }

    /**
     * Get the entity_type_id for catalog_category
     */
    private function getCategoryEntityTypeId(): int
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('eav_entity_type');

        return (int)$connection->fetchOne(
            $connection->select()
                ->from($table, ['entity_type_id'])
                ->where('entity_type_code = ?', 'catalog_category')
        );
    }

    /**
     * Ensure each <url> block has <changefreq> and <priority> tags
     * Adds default values if missing
     */
    private function ensureUrlTags(array $urlBlocks): array
    {
        return array_map(function (string $block) {
            if (strpos($block, '<changefreq>') === false) {
                $block = str_replace('</url>', '<changefreq>daily</changefreq></url>', $block);
            }
            if (strpos($block, '<priority>') === false) {
                $block = str_replace('</url>', '<priority>0.5</priority></url>', $block);
            }
            return $block;
        }, $urlBlocks);
    }

    /**
     * Build a sitemap XML string from URL blocks
     */
    private function buildSitemapXml(string $urlsetAttrs, array $urlBlocks): string
    {
        $urlBlocks = $this->ensureUrlTags($urlBlocks);
        $content = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $content .= '<urlset' . $urlsetAttrs . '>' . PHP_EOL;
        $content .= implode(PHP_EOL, $urlBlocks) . PHP_EOL;
        $content .= '</urlset>' . PHP_EOL;

        return $content;
    }

    /**
     * Clean up old sitemap split files no longer in use
     */
    private function cleanupOldFiles(string $pubDir, array $currentFiles): void
    {
        $patterns = [
            $pubDir . '/sitemap-*.xml',
            $pubDir . '/sitemap-cat-*.xml',
        ];

        $existingFiles = [];
        foreach ($patterns as $pattern) {
            $existingFiles = array_merge($existingFiles, glob($pattern) ?: []);
        }
        $existingFiles = array_unique($existingFiles);

        foreach ($existingFiles as $filePath) {
            $filename = basename($filePath);

            if ($filename === 'sitemap-original.xml') {
                continue;
            }

            if (!in_array($filename, $currentFiles, true)) {
                unlink($filePath);
                $this->logger->info('Klever_Sitemap: Removed old file: ' . $filename);
            }
        }
    }

    /**
     * Get CMS page slugs dynamically from database (enabled pages only)
     */
    private function getCmsSlugs(): array
    {
        if ($this->cmsSlugs !== null) {
            return $this->cmsSlugs;
        }

        $this->cmsSlugs = ['/'];

        $collection = $this->cmsPageCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);

        foreach ($collection as $page) {
            $identifier = $page->getIdentifier();
            if ($identifier) {
                $this->cmsSlugs[] = '/' . ltrim($identifier, '/');
            }
        }

        $this->logger->info(sprintf(
            'Klever_Sitemap: Loaded %d enabled CMS page slugs from database',
            count($this->cmsSlugs)
        ));

        return $this->cmsSlugs;
    }

    /**
     * Check if URL is a CMS/static page
     */
    private function isCmsPage(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return false;
        }

        $path = preg_replace('#^/[a-z]{2}(/|$)#', '/', $path);
        $path = rtrim($path, '/');

        if ($path === '') {
            $path = '/';
        }

        $cmsSlugs = $this->getCmsSlugs();
        foreach ($cmsSlugs as $slug) {
            $slug = rtrim($slug, '/');
            if ($slug === '') {
                $slug = '/';
            }
            if ($path === $slug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize category name for use as a filename
     */
    private function sanitizeFilename(string $name): string
    {
        $name = mb_strtolower($name, 'UTF-8');
        $name = preg_replace('/[^a-z0-9]+/', '-', $name);
        $name = trim($name, '-');
        if (strlen($name) > 50) {
            $name = substr($name, 0, 50);
            $name = rtrim($name, '-');
        }

        return $name ?: 'unknown';
    }

    /**
     * Find a valid source sitemap file that contains <url> blocks (not a sitemapindex)
     */
    private function findSourceSitemap(string $pubDir, string $sitemapFile, string $backupFile): ?string
    {
        foreach ([$backupFile, $sitemapFile] as $candidate) {
            if (!file_exists($candidate)) {
                continue;
            }
            $header = file_get_contents($candidate, false, null, 0, 500);
            if (strpos($header, '<urlset') !== false) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Create sitemap index file pointing to all split sitemaps
     */
    private function createSitemapIndex(string $pubDir, array $files): void
    {
        $baseUrl = 'https://www.tyrescart.com';
        foreach ($files as $file) {
            $filePath = $pubDir . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath, false, null, 0, 500);
                if (preg_match('/<loc>(https?:\/\/[^\/]+)/', $content, $m)) {
                    $baseUrl = $m[1];
                    break;
                }
            }
        }

        $lastmod = date('Y-m-d\TH:i:s+00:00');

        $index = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($files as $file) {
            $index .= '  <sitemap>' . PHP_EOL;
            $index .= '    <loc>' . $baseUrl . '/' . $file . '</loc>' . PHP_EOL;
            $index .= '    <lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
            $index .= '  </sitemap>' . PHP_EOL;
        }

        $index .= '</sitemapindex>' . PHP_EOL;

        $originalBackup = $pubDir . '/sitemap-original.xml';
        $mainSitemap = $pubDir . '/sitemap.xml';

        if (file_exists($mainSitemap)) {
            $header = file_get_contents($mainSitemap, false, null, 0, 500);
            if (strpos($header, '<urlset') !== false) {
                copy($mainSitemap, $originalBackup);
                $this->logger->info('Klever_Sitemap: Backed up original sitemap.xml');
            }
        }

        file_put_contents($mainSitemap, $index);

        $this->logger->info('Klever_Sitemap: Sitemap index written to sitemap.xml');
    }
}
