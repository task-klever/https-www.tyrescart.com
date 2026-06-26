<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model;

use Hdweb\GenerateUrl\Api\GeneratorInterface;
use Hdweb\GenerateUrl\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\State;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Exception\FileSystemException;

class Generator implements GeneratorInterface
{
    /**
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * @param Data $helper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CmsPageCollectionFactory $cmsPageCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param State $appState
     * @param ProductHelper $productHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        private readonly Data $helper,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly CmsPageCollectionFactory $cmsPageCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly State $appState,
        private readonly ProductHelper $productHelper,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly LoggerInterface $logger,
        Filesystem $filesystem,
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function generate(?int $storeId = null): string
    {
        if (!$this->helper->isEnabled($storeId)) {
            return '';
        }

        $storeId = $storeId ?: (int) $this->storeManager->getStore()->getId();
        
        // Check and log missing extensions
        $this->checkAndLogMissingExtensions($storeId);
        
        $content = [];

        // Add header information
        $content[] = $this->getHeaderContent($storeId);

        // Add products
        if ($this->helper->isProductsEnabled($storeId)) {
            $content[] = $this->getProductsContent($storeId);
        }

        // Add categories
        if ($this->helper->isCategoriesEnabled($storeId)) {
            $content[] = $this->getCategoriesContent($storeId);
        }

        // Add CMS pages
        if ($this->helper->isCmsEnabled($storeId)) {
            $content[] = $this->getCmsContent($storeId);
        }

        // Add blog posts
        if ($this->helper->isBlogEnabled($storeId)) {
            $blogContent = $this->getBlogContent($storeId);
            if ($blogContent) {
                $content[] = $blogContent;
            }
        }

        // Add brand pages
        if ($this->helper->isBrandEnabled($storeId)) {
            $brandContent = $this->getBrandContent($storeId);
            if ($brandContent) {
                $content[] = $brandContent;
            }
        }

        // Add vehicle pages
        if ($this->helper->isVehicleEnabled($storeId)) {
            $vehicleContent = $this->getVehicleContent($storeId);
            if ($vehicleContent) {
                $content[] = $vehicleContent;
            }
        }

        // Add additional pages
        $additionalPages = $this->helper->getConfigValue(Data::XML_PATH_ADDITIONAL_PAGES, $storeId);
        if ($additionalPages) {
            $content[] = "\n## Additional Pages\n\n" . trim($additionalPages);
        }

        return implode("\n", array_filter($content));
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getHeaderContent(int $storeId): string
    {
        $lines = [];

        $companyName = $this->helper->getConfigValue(Data::XML_PATH_COMPANY_NAME, $storeId);
        if ($companyName) {
            $lines[] = "# " . trim($companyName);
        }

        $companyDescription = $this->helper->getConfigValue(Data::XML_PATH_COMPANY_DESCRIPTION, $storeId);
        if ($companyDescription) {
            $lines[] = "\n" . trim($companyDescription);
        }

        $additionalInfo = $this->helper->getConfigValue(Data::XML_PATH_ADDITIONAL_INFORMATION, $storeId);
        if ($additionalInfo) {
            $lines[] = "\n" . trim($additionalInfo);
        }

        return implode("\n", $lines);
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getProductsContent(int $storeId): string
    {
        $sortOrder = (int) ($this->helper->getConfigValue(Data::XML_PATH_PRODUCT_SORT_ORDER, $storeId) ?: 1);
        $contentFields = $this->helper->getConfigValue(Data::XML_PATH_PRODUCT_CONTENT_FIELDS, $storeId);
        $contentFields = is_string($contentFields) ? explode(',', $contentFields) : [];
        $excludeIds = $this->getExcludeIds(
            $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_PRODUCT_IDS, $storeId)
        );
        $excludeSkus = $this->getExcludeSkus(
            $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_PRODUCT_SKUS, $storeId)
        );

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect([
                'name',
                'url_key',
                'meta_description',
                'meta_title',
                'meta_keywords',
                'short_description',
                'sku',
                'description',
                'price',
            ])
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
            ]])
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addFinalPrice();

        if (!empty($excludeIds)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $excludeIds]);
        }

        if (!empty($excludeSkus)) {
            $collection->addAttributeToFilter('sku', ['nin' => $excludeSkus]);
        }

        $collection->setOrder('entity_id', 'ASC');

        $lines = ["\n## Products\n"];
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK);

        foreach ($collection as $product) {
            $product->setStoreId($storeId);
            $url = $this->productHelper->getProductUrl($product);
            $name = $product->getName();
            
            $line = "- [{$name}]({$url}):";
            $lines[] = $line;
            
            // Process all selected content fields - only display if actual data exists
            foreach ($contentFields as $field) {
                $field = trim($field);
                
                // Handle price field
                if ($field === 'price') {
                    $priceValue = $this->getProductPriceValue($product, $field, $storeId);
                    if ($priceValue && trim($priceValue) !== '') {
                        $lines[] = "**Price**: {$priceValue}";
                    }
                    continue;
                }
                
                // Handle other fields - get actual product data, skip if empty
                $value = $this->getProductFieldValue($product, $field, $storeId);
                if ($value && trim($value) !== '') {
                    $label = $this->getFieldLabel($field);
                    $lines[] = "**{$label}**: {$value}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get product price value
     *
     * @param $product
     * @param string $priceType
     * @param int $storeId
     * @return string
     */
    private function getProductPriceValue($product, string $priceType, int $storeId): string
    {
        try {
            $priceValue = $product->getFinalPrice();

            if ($priceValue && $priceValue > 0) {
                return $this->priceCurrency->format(
                    $priceValue,
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $storeId
                );
            }
        } catch (\Exception $e) {
            // Price not available
        }

        return '';
    }


    /**
     * @param int $storeId
     * @return string
     */
    private function getCategoriesContent(int $storeId): string
    {
        $sortOrder = (int) ($this->helper->getConfigValue(Data::XML_PATH_CATEGORY_SORT_ORDER, $storeId) ?: 2);
        $contentFields = $this->helper->getConfigValue(Data::XML_PATH_CATEGORY_CONTENT_FIELDS, $storeId);
        $contentFields = is_string($contentFields) ? explode(',', $contentFields) : [];
        $excludeIds = $this->getExcludeIds(
            $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_CATEGORY_IDS, $storeId)
        );

        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect([
                'name',
                'url_key',
                'meta_description',
                'meta_title',
                'meta_keywords',
                'description',
            ])
            ->addAttributeToFilter('is_active', 1)
            ->setStoreId($storeId);

        if (!empty($excludeIds)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $excludeIds]);
        }

        $collection->setOrder('entity_id', 'ASC');

        $lines = ["\n## Categories\n"];
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK);

        foreach ($collection as $category) {
            if ($category->getLevel() < 2) {
                continue;
            }

            $category->setStoreId($storeId);
            $url = $category->getUrl();
            $name = $category->getName();
            
            $line = "- [{$name}]({$url})";
            $lines[] = $line;
            
            // Process all selected content fields
            foreach ($contentFields as $field) {
                $field = trim($field);
                $value = $this->getCategoryFieldValue($category, $field, $storeId);
                if ($value && trim($value) !== '') {
                    $label = $this->getFieldLabel($field);
                    $lines[] = "**{$label}**: {$value}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getCmsContent(int $storeId): string
    {
        $sortOrder = (int) ($this->helper->getConfigValue(Data::XML_PATH_CMS_SORT_ORDER, $storeId) ?: 3);
        $contentFields = $this->helper->getConfigValue(Data::XML_PATH_CMS_CONTENT_FIELDS, $storeId);
        $contentFields = is_string($contentFields) ? explode(',', $contentFields) : [];
        $excludeIds = $this->getExcludeIds(
            $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_CMS_PAGES, $storeId)
        );

        $collection = $this->cmsPageCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter([$storeId, 0]);

        if (!empty($excludeIds)) {
            $collection->addFieldToFilter('page_id', ['nin' => $excludeIds]);
        }

        $collection->setOrder('page_id', 'ASC');

        $lines = ["\n## CMS Pages\n"];
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK);
        $storeCode = $this->storeManager->getStore($storeId)->getCode();

        foreach ($collection as $page) {
            $identifier = $page->getIdentifier();
            $url = $baseUrl . $identifier;
            $title = $page->getTitle();
            
            $line = "- [{$title}]({$url})";
            $lines[] = $line;
            
            // Process all selected content fields
            foreach ($contentFields as $field) {
                $field = trim($field);
                $value = $this->getCmsFieldValue($page, $field, $storeId);
                if ($value && trim($value) !== '') {
                    $label = $this->getFieldLabel($field);
                    $lines[] = "**{$label}**: {$value}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get product field value - retrieves actual product data
     *
     * @param $product
     * @param string $field
     * @param int $storeId
     * @return string
     */
    private function getProductFieldValue($product, string $field, int $storeId): string
    {
        // Skip price field as it is handled separately
        if ($field === 'price') {
            return '';
        }

        // Get actual product attribute values
        $value = match ($field) {
            'page_name' => (string) $product->getName(),
            'short_description' => (string) $product->getShortDescription(),
            'sku' => (string) $product->getSku(),
            'meta_title' => (string) $product->getMetaTitle(),
            'meta_keywords' => (string) $product->getMetaKeywords(),
            'description' => (string) $product->getDescription(),
            default => '',
        };

        // Return empty if no value, otherwise return cleaned value
        if (empty($value) || trim($value) === '') {
            return '';
        }

        return $this->stripHtml($value);
    }

    /**
     * Get category field value - retrieves actual category data
     *
     * @param $category
     * @param string $field
     * @param int $storeId
     * @return string
     */
    private function getCategoryFieldValue($category, string $field, int $storeId): string
    {
        // Get actual category attribute values
        $value = match ($field) {
            'description' => (string) $category->getDescription(),
            'meta_title' => (string) $category->getMetaTitle(),
            'meta_keywords' => (string) $category->getMetaKeywords(),
            default => '',
        };

        // Return empty if no value, otherwise return cleaned value
        if (empty($value) || trim($value) === '') {
            return '';
        }

        return $this->stripHtml($value);
    }

    /**
     * Get CMS page field value - retrieves actual page data
     *
     * @param $page
     * @param string $field
     * @param int $storeId
     * @return string
     */
    private function getCmsFieldValue($page, string $field, int $storeId): string
    {
        // Get actual CMS page attribute values
        $value = match ($field) {
            'title' => (string) $page->getTitle(),
            'meta_title' => (string) $page->getMetaTitle(),
            'meta_description' => (string) $page->getMetaDescription(),
            'meta_keywords' => (string) $page->getMetaKeywords(),
            'content' => (string) $page->getContent(),
            default => '',
        };

        // Return empty if no value, otherwise return cleaned value
        if (empty($value) || trim($value) === '') {
            return '';
        }

        return $this->stripHtml($value);
    }

    /**
     * @param string $field
     * @return string
     */
    private function getFieldLabel(string $field): string
    {
        return match ($field) {
            'page_name' => 'Page Name',
            'short_description' => 'Short Description',
            'sku' => 'SKU',
            'meta_title' => 'Meta Title',
            'meta_keywords' => 'Meta Keywords',
            'description' => 'Description',
            'meta_description' => 'Meta Description',
            'content' => 'Content',
            'title' => 'Title',
            'price' => 'Price',
            'short_content' => 'Short Content',
            'make_paragraph1' => 'Make Paragraph 1',
            'make_paragraph2' => 'Make Paragraph 2',
            'model_paragraph1' => 'Model Paragraph 1',
            'model_paragraph2' => 'Model Paragraph 2',
            'model_paragraph3' => 'Model Paragraph 3',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }

    /**
     * @param string|null $ids
     * @return array
     */
    private function getExcludeIds(?string $ids): array
    {
        if (!$ids) {
            return [];
        }

        $ids = explode(',', $ids);
        return array_filter(array_map('trim', $ids));
    }

    /**
     * Get exclude SKUs from comma-separated string
     *
     * @param string|null $skus
     * @return array
     */
    private function getExcludeSkus(?string $skus): array
    {
        if (!$skus) {
            return [];
        }

        $skus = explode(',', $skus);
        return array_filter(array_map('trim', $skus));
    }

    /**
     * Strip HTML tags and decode entities
     *
     * @param string $value
     * @return string
     */
    private function stripHtml(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        return $value;
    }

    /**
     * Get blog posts content
     *
     * @param int $storeId
     * @return string
     */
    private function getBlogContent(int $storeId): string
    {
        if (!class_exists(\MGS\Blog\Model\Post::class)) {
            return '';
        }

        try {
            $contentFields = $this->helper->getConfigValue(Data::XML_PATH_BLOG_CONTENT_FIELDS, $storeId);
            $contentFields = is_string($contentFields) ? explode(',', $contentFields) : [];
            $excludeIds = $this->getExcludeIds(
                $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_BLOG_IDS, $storeId)
            );

            $postModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\MGS\Blog\Model\Post::class);
            $collection = $postModel->getCollection()
                ->addFieldToFilter('status', \MGS\Blog\Model\Post::STATUS_ENABLED)
                ->addStoreFilter($storeId);

            if (!empty($excludeIds)) {
                $collection->addFieldToFilter('post_id', ['nin' => $excludeIds]);
            }

            $collection->setOrder('post_id', 'ASC');

            $lines = ["\n## Blog Posts\n"];

            foreach ($collection as $post) {
                $url = $post->getPostUrlWithNoCategory();
                $title = $post->getTitle();
                
                $line = "- [{$title}]({$url})";
                $lines[] = $line;
                
                // Process all selected content fields
                foreach ($contentFields as $field) {
                    $field = trim($field);
                    $value = $this->getBlogFieldValue($post, $field);
                    if ($value && trim($value) !== '') {
                        $label = $this->getFieldLabel($field);
                        $lines[] = "**{$label}**: {$value}";
                    }
                }
            }

            return implode("\n", $lines);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get blog field value
     *
     * @param mixed $post
     * @param string $field
     * @return string
     */
    private function getBlogFieldValue($post, string $field): string
    {
        $value = '';
        
        switch ($field) {
            case 'short_content':
                $value = $post->getShortContent();
                break;
            case 'content':
                $value = $post->getContent();
                break;
            case 'meta_title':
                $value = $post->getMetaTitle();
                break;
            case 'meta_keywords':
                $value = $post->getMetaKeywords();
                break;
            case 'meta_description':
                $value = $post->getMetaDescription();
                break;
        }

        return $this->stripHtml((string) $value);
    }

    /**
     * Get brand pages content (includes regular brands and vehicle pages)
     *
     * @param int $storeId
     * @return string
     */
    private function getBrandContent(int $storeId): string
    {
        $lines = [];
        $hasContent = false;

        // Add regular brand pages
        if (class_exists(\MGS\Brand\Model\Brand::class)) {
            try {
                $contentFields = $this->helper->getConfigValue(Data::XML_PATH_BRAND_CONTENT_FIELDS, $storeId);
                $contentFields = is_string($contentFields) ? explode(',', $contentFields) : [];
                $excludeIds = $this->getExcludeIds(
                    $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_BRAND_IDS, $storeId)
                );

                $brandModel = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\MGS\Brand\Model\Brand::class);
                $collection = $brandModel->getCollection()
                    ->addFieldToFilter('status', \MGS\Brand\Model\Brand::STATUS_ENABLED)
                    ->addStoreFilter($storeId);

                if (!empty($excludeIds)) {
                    $collection->addFieldToFilter('brand_id', ['nin' => $excludeIds]);
                }

                $collection->setOrder('brand_id', 'ASC');

                foreach ($collection as $brand) {
                    $url = $brand->getUrl();
                    $name = $brand->getName();
                    
                    $line = "- [{$name}]({$url})";
                    $lines[] = $line;
                    $hasContent = true;
                    
                    // Process all selected content fields
                    foreach ($contentFields as $field) {
                        $field = trim($field);
                        $value = $this->getBrandFieldValue($brand, $field);
                        if ($value && trim($value) !== '') {
                            $label = $this->getFieldLabel($field);
                            $lines[] = "**{$label}**: {$value}";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to vehicle pages
            }
        }

        if (!$hasContent) {
            return '';
        }

        return "\n## Brands\n" . implode("\n", $lines);
    }

    /**
     * Get vehicle pages content (car brands and models)
     *
     * @param int $storeId
     * @return string
     */
    private function getVehicleContent(int $storeId): string
    {
        $lines = [];
        $hasContent = false;

        // Add vehicle pages (car brands)
        try {
            if (class_exists(\Hdweb\Vehicles\Model\Vehicles::class)) {
                $vehicleLines = $this->getVehicleBrandLines($storeId);
                if (!empty($vehicleLines)) {
                    $lines = array_merge($lines, $vehicleLines);
                    $hasContent = true;
                }
            }
        } catch (\Exception $e) {
            // Continue even if Vehicles module has issues
        }

        if (!$hasContent) {
            return '';
        }

        return "\n## Vehicle Pages\n" . implode("\n", $lines);
    }

    /**
     * Get vehicle brand lines (for inclusion in Brands section)
     *
     * @param int $storeId
     * @return array
     */
    private function getVehicleBrandLines(int $storeId): array
    {
        $lines = [];
        $contentFields = $this->helper->getConfigValue(Data::XML_PATH_VEHICLE_CONTENT_FIELDS, $storeId);
        $contentFields = is_string($contentFields) ? explode(',', $contentFields) : [];
        
        $excludeIds = $this->getExcludeIds(
            $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_VEHICLE_IDS, $storeId)
        );
        
        $excludeMakes = $this->getExcludeIds(
            $this->helper->getConfigValue(Data::XML_PATH_EXCLUDE_VEHICLE_MAKES, $storeId)
        );
        // Convert exclude makes to lowercase for case-insensitive comparison
        $excludeMakes = array_map('strtolower', array_map('trim', $excludeMakes));

        try {
            $vehicleModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Hdweb\Vehicles\Model\Vehicles::class);
            $collectionFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Hdweb\Vehicles\Model\ResourceModel\Vehicles\CollectionFactory::class);

            $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK);
            $route = 'tyres/cars';

            // Get unique makes (make-only pages)
            // Note: store_id is stored as TEXT, so convert to string for comparison
            // Include both specific store and '0' (all stores) like Magento CMS pages
            $storeIdStr = (string) $storeId;
            $makeCollection = $collectionFactory->create()
                ->addFieldToSelect(['make', 'meta_title', 'meta_keywords', 'meta_description', 'make_paragraph1', 'make_paragraph2', 'vehicles_id', 'store_id'])
                ->addFieldToFilter('make', ['neq' => 'NULL'])
                ->addFieldToFilter('make', ['neq' => ''])
                ->addFieldToFilter(
                    'model',
                    [
                        ['null' => true],
                        ['eq' => ''],
                    ]
                )
                ->addFieldToFilter('store_id', ['in' => [$storeIdStr, '0']]);

            // Order by store_id first (prefer store-specific over '0'), then by data completeness
            // We'll handle data completeness in PHP, but order by store_id to prefer store-specific records
            $makeCollection->setOrder('store_id', 'DESC'); // Store-specific first (storeId > '0')
            $makeCollection->setOrder('make', 'ASC');
            $makeCollection->setOrder('vehicles_id', 'ASC');

            // Group makes manually and prefer records with complete data
            $makes = [];
            foreach ($makeCollection as $makeItem) {
                $make = $makeItem->getData('make');
                if ($make && trim($make) !== '') {
                    $make = trim($make);
                    // Only add if we haven't seen this make before, or if this record has more complete data
                    if (!isset($makes[$make])) {
                        $makes[$make] = $makeItem;
                    } else {
                        // Score records based on data completeness
                        $existingScore = $this->getVehicleDataCompletenessScore($makes[$make]);
                        $currentScore = $this->getVehicleDataCompletenessScore($makeItem);
                        
                        // Also prefer store-specific records over '0' (all stores)
                        $existingStoreId = $makes[$make]->getData('store_id');
                        $currentStoreId = $makeItem->getData('store_id');
                        $existingIsStoreSpecific = ($existingStoreId == $storeIdStr);
                        $currentIsStoreSpecific = ($currentStoreId == $storeIdStr);
                        
                        // Prefer record with higher completeness score, or store-specific if scores are equal
                        if ($currentScore > $existingScore) {
                            $makes[$make] = $makeItem;
                        } elseif ($currentScore == $existingScore && $currentIsStoreSpecific && !$existingIsStoreSpecific) {
                            $makes[$make] = $makeItem;
                        }
                    }
                }
            }
            
            // For makes that don't have meta data, try to find a record with data from any store
            foreach ($makes as $make => $makeItem) {
                $hasMetaData = $makeItem->getData('meta_description') || 
                              $makeItem->getData('meta_keywords') || 
                              $makeItem->getData('make_paragraph1') || 
                              $makeItem->getData('make_paragraph2');
                
                if (!$hasMetaData) {
                    // Try to find a record with data for this make
                    $dataCollection = $collectionFactory->create()
                        ->addFieldToSelect(['make', 'meta_title', 'meta_keywords', 'meta_description', 'make_paragraph1', 'make_paragraph2', 'vehicles_id', 'store_id'])
                        ->addFieldToFilter('make', $make)
                        ->addFieldToFilter(
                            'model',
                            [
                                ['null' => true],
                                ['eq' => ''],
                            ]
                        );
                    
                    // Add OR condition for fields with data
                    $dataCollection->getSelect()->where(
                        '(meta_description IS NOT NULL AND meta_description != "") OR ' .
                        '(meta_keywords IS NOT NULL AND meta_keywords != "") OR ' .
                        '(make_paragraph1 IS NOT NULL AND make_paragraph1 != "")'
                    );
                    
                    $dataCollection->setOrder('store_id', 'DESC')
                        ->setOrder('vehicles_id', 'ASC')
                        ->setPageSize(1);
                    
                    if ($dataCollection->getSize() > 0) {
                        $makes[$make] = $dataCollection->getFirstItem();
                    }
                }
            }

            // Process make pages
            foreach ($makes as $make => $makeItem) {
                // Make value from database - use directly (already in correct format like "mitsubishi")
                // Based on VehiclesPages.php, make values are used directly: $route . '/' . $makeData->getMake()
                $makeSlug = trim($make);
                
                // Check if this make should be excluded
                if (in_array(strtolower($makeSlug), $excludeMakes)) {
                    continue;
                }
                
                // Load the full model to ensure all data is available
                $vehicleId = $makeItem->getData('vehicles_id');
                if ($vehicleId) {
                    // Check if this vehicle ID should be excluded
                    if (in_array((int) $vehicleId, $excludeIds)) {
                        continue;
                    }
                    
                    $fullVehicle = $vehicleModel->load($vehicleId);
                    if ($fullVehicle && $fullVehicle->getId()) {
                        $makeItem = $fullVehicle;
                    }
                }
                
                $url = $baseUrl . $route . '/' . $makeSlug;
                // Display name: convert slug to readable format
                $makeName = ucwords(str_replace(['-', '_'], ' ', $makeSlug));
                
                $line = "- [{$makeName}]({$url})";
                $lines[] = $line;
                
                // Process all selected content fields (same as regular brands)
                if (!empty($contentFields)) {
                    foreach ($contentFields as $field) {
                        $field = trim($field);
                        if (empty($field)) {
                            continue;
                        }
                        $value = $this->getVehicleMakeFieldValue($makeItem, $field);
                        if ($value && trim($value) !== '') {
                            $label = $this->getFieldLabel($field);
                            $lines[] = "**{$label}**: {$value}";
                        }
                    }
                }
            }

            // Process model pages (make + model)
            foreach ($makes as $make => $makeItem) {
                // Make value from database - use directly
                $makeSlug = trim($make);
                
                // Get models for this make
                // Note: store_id is stored as TEXT, so convert to string for comparison
                // Include both specific store and '0' (all stores) like Magento CMS pages
                $modelCollection = $collectionFactory->create()
                    ->addFieldToSelect(['make', 'model', 'meta_title', 'meta_keywords', 'meta_description', 'model_paragraph1', 'model_paragraph2', 'model_paragraph3'])
                    ->addFieldToFilter('store_id', ['in' => [$storeIdStr, '0']])
                    ->addFieldToFilter('make', $make)
                    ->addFieldToFilter(
                        'model',
                        [
                            ['notnull' => true],
                            ['neq' => '']
                        ]
                    );

                // Group by model to get unique models
                $modelCollection->getSelect()->group('model');
                $modelCollection->setOrder('model', 'ASC');

            foreach ($modelCollection as $modelItem) {
                $model = $modelItem->getData('model');
                if ($model && trim($model) !== '') {
                    // Load the full model to ensure all data is available
                    $vehicleId = $modelItem->getData('vehicles_id');
                    if ($vehicleId) {
                        // Check if this vehicle ID should be excluded
                        if (in_array((int) $vehicleId, $excludeIds)) {
                            continue;
                        }
                        
                        $fullVehicle = $vehicleModel->load($vehicleId);
                        if ($fullVehicle && $fullVehicle->getId()) {
                            $modelItem = $fullVehicle;
                        }
                    }
                    
                    // Model value from database - use directly (already in correct format like "lancer-evolution")
                    // Based on VehiclesPages.php, model values are used directly: $makeUrl . '/' . $modelData->getModel()
                    $modelSlug = trim($model);
                    
                    $url = $baseUrl . $route . '/' . $makeSlug . '/' . $modelSlug;
                    // Display names: convert slugs to readable format
                    $modelName = ucwords(str_replace(['-', '_'], ' ', $modelSlug));
                    $makeName = ucwords(str_replace(['-', '_'], ' ', $makeSlug));
                    
                    $line = "- [{$makeName} {$modelName}]({$url})";
                    $lines[] = $line;
                    
                    // Process all selected content fields (same as regular brands)
                    if (!empty($contentFields)) {
                        foreach ($contentFields as $field) {
                            $field = trim($field);
                            if (empty($field)) {
                                continue;
                            }
                            $value = $this->getVehicleModelFieldValue($modelItem, $field);
                            if ($value && trim($value) !== '') {
                                $label = $this->getFieldLabel($field);
                                $lines[] = "**{$label}**: {$value}";
                            }
                        }
                    }
                }
            }
            }

        } catch (\Exception $e) {
            // Log error but return empty array to prevent breaking the generation
            // In production, you might want to log this: $this->logger->error('Vehicle data error: ' . $e->getMessage());
        }

        return $lines;
    }

    /**
     * Get vehicle make field value (for make pages)
     *
     * @param mixed $vehicle
     * @param string $field
     * @return string
     */
    private function getVehicleMakeFieldValue($vehicle, string $field): string
    {
        $value = '';
        
        switch ($field) {
            case 'description':
                // Combine make_paragraph1 and make_paragraph2 as description
                $paragraph1 = $vehicle->getData('make_paragraph1');
                $paragraph2 = $vehicle->getData('make_paragraph2');
                $parts = array_filter([$paragraph1, $paragraph2]);
                $value = implode(' ', $parts);
                break;
            case 'meta_title':
                $value = $vehicle->getData('meta_title');
                break;
            case 'meta_keywords':
                $value = $vehicle->getData('meta_keywords');
                break;
            case 'meta_description':
                $value = $vehicle->getData('meta_description');
                break;
            case 'make_paragraph1':
                $value = $vehicle->getData('make_paragraph1');
                break;
            case 'make_paragraph2':
                $value = $vehicle->getData('make_paragraph2');
                break;
        }

        return $this->stripHtml((string) $value);
    }

    /**
     * Get vehicle model field value (for model pages)
     *
     * @param mixed $vehicle
     * @param string $field
     * @return string
     */
    private function getVehicleModelFieldValue($vehicle, string $field): string
    {
        $value = '';
        
        switch ($field) {
            case 'description':
                // Combine model_paragraph1, model_paragraph2, and model_paragraph3 as description
                $paragraph1 = $vehicle->getData('model_paragraph1');
                $paragraph2 = $vehicle->getData('model_paragraph2');
                $paragraph3 = $vehicle->getData('model_paragraph3');
                $parts = array_filter([$paragraph1, $paragraph2, $paragraph3]);
                $value = implode(' ', $parts);
                break;
            case 'meta_title':
                $value = $vehicle->getData('meta_title');
                break;
            case 'meta_keywords':
                $value = $vehicle->getData('meta_keywords');
                break;
            case 'meta_description':
                $value = $vehicle->getData('meta_description');
                break;
            case 'model_paragraph1':
                $value = $vehicle->getData('model_paragraph1');
                break;
            case 'model_paragraph2':
                $value = $vehicle->getData('model_paragraph2');
                break;
            case 'model_paragraph3':
                $value = $vehicle->getData('model_paragraph3');
                break;
        }

        return $this->stripHtml((string) $value);
    }

    /**
     * Get vehicle data completeness score (higher = more complete)
     *
     * @param mixed $vehicle
     * @return int
     */
    private function getVehicleDataCompletenessScore($vehicle): int
    {
        $score = 0;
        
        // Check for meta fields (higher priority)
        if ($vehicle->getData('meta_description')) {
            $score += 10;
        }
        if ($vehicle->getData('meta_keywords')) {
            $score += 5;
        }
        if ($vehicle->getData('meta_title')) {
            $score += 5;
        }
        
        // Check for paragraph fields
        if ($vehicle->getData('make_paragraph1')) {
            $score += 3;
        }
        if ($vehicle->getData('make_paragraph2')) {
            $score += 3;
        }
        if ($vehicle->getData('model_paragraph1')) {
            $score += 3;
        }
        if ($vehicle->getData('model_paragraph2')) {
            $score += 3;
        }
        if ($vehicle->getData('model_paragraph3')) {
            $score += 3;
        }
        
        return $score;
    }

    /**
     * Get brand field value
     *
     * @param mixed $brand
     * @param string $field
     * @return string
     */
    private function getBrandFieldValue($brand, string $field): string
    {
        $value = '';
        
        switch ($field) {
            case 'description':
                $value = $brand->getDescription();
                break;
            case 'meta_title':
                $value = $brand->getMetaTitle();
                break;
            case 'meta_keywords':
                $value = $brand->getMetaKeywords();
                break;
            case 'meta_description':
                $value = $brand->getMetaDescription();
                break;
        }

        return $this->stripHtml((string) $value);
    }

    /**
     * Check for missing extensions and generate log file
     *
     * @param int $storeId
     * @return void
     */
    private function checkAndLogMissingExtensions(int $storeId): void
    {
        $missingExtensions = [];
        $availableExtensions = [];

        // Check Hdweb\Vehicles extension
        if (!class_exists(\Hdweb\Vehicles\Model\Vehicles::class)) {
            $missingExtensions[] = [
                'name' => 'Hdweb\Vehicles',
                'module' => 'Hdweb_Vehicles',
                'description' => 'Vehicle pages (car brands and models) will not be included in the LLMs.txt file.',
            ];
        } else {
            $availableExtensions[] = [
                'name' => 'Hdweb\Vehicles',
                'module' => 'Hdweb_Vehicles',
                'status' => 'Available',
            ];
        }

        // Check MGS\Brand extension
        if (!class_exists(\MGS\Brand\Model\Brand::class)) {
            $missingExtensions[] = [
                'name' => 'MGS\Brand',
                'module' => 'MGS_Brand',
                'description' => 'Brand pages will not be included in the LLMs.txt file.',
            ];
        } else {
            $availableExtensions[] = [
                'name' => 'MGS\Brand',
                'module' => 'MGS_Brand',
                'status' => 'Available',
            ];
        }

        // Check MGS\Blog extension
        if (!class_exists(\MGS\Blog\Model\Post::class)) {
            $missingExtensions[] = [
                'name' => 'MGS\Blog',
                'module' => 'MGS_Blog',
                'description' => 'Blog posts will not be included in the LLMs.txt file.',
            ];
        } else {
            $availableExtensions[] = [
                'name' => 'MGS\Blog',
                'module' => 'MGS_Blog',
                'status' => 'Available',
            ];
        }

        // Generate log file if any extensions are missing
        if (!empty($missingExtensions)) {
            $this->generateExtensionLogFile($missingExtensions, $availableExtensions, $storeId);
        }
    }

    /**
     * Generate log file with missing extension information
     *
     * @param array $missingExtensions
     * @param array $availableExtensions
     * @param int $storeId
     * @return void
     */
    private function generateExtensionLogFile(
        array $missingExtensions,
        array $availableExtensions,
        int $storeId
    ): void {
        try {
            $logDir = $this->varDirectory->getAbsolutePath('log');
            
            // Ensure log directory exists
            if (!$this->varDirectory->isDirectory('log')) {
                $this->varDirectory->create('log');
            }

            $timestamp = date('Y-m-d H:i:s');
            $storeName = $this->storeManager->getStore($storeId)->getName();
            
            $logContent = "========================================\n";
            $logContent .= "LLMs.txt Generator - Extension Status Log\n";
            $logContent .= "========================================\n\n";
            $logContent .= "Generated At: {$timestamp}\n";
            $logContent .= "Store ID: {$storeId}\n";
            $logContent .= "Store Name: {$storeName}\n\n";
            
            if (!empty($missingExtensions)) {
                $logContent .= "MISSING EXTENSIONS:\n";
                $logContent .= "==================\n\n";
                
                foreach ($missingExtensions as $index => $extension) {
                    $logContent .= ($index + 1) . ". Extension Name: {$extension['name']}\n";
                    $logContent .= "   Module Name: {$extension['module']}\n";
                    $logContent .= "   Status: NOT INSTALLED\n";
                    $logContent .= "   Impact: {$extension['description']}\n\n";
                }
                
                $logContent .= "\n";
            }
            
            if (!empty($availableExtensions)) {
                $logContent .= "AVAILABLE EXTENSIONS:\n";
                $logContent .= "=====================\n\n";
                
                foreach ($availableExtensions as $index => $extension) {
                    $logContent .= ($index + 1) . ". Extension Name: {$extension['name']}\n";
                    $logContent .= "   Module Name: {$extension['module']}\n";
                    $logContent .= "   Status: {$extension['status']}\n\n";
                }
            }
            
            $logContent .= "\n";
            $logContent .= "========================================\n";
            $logContent .= "Note: This log file is automatically generated when missing extensions are detected.\n";
            $logContent .= "To include data from missing extensions, please install and enable them.\n";
            $logContent .= "========================================\n";

            $logFilename = 'llms_txt_missing_extensions.log';
            $logPath = 'log/' . $logFilename;
            
            $this->varDirectory->writeFile($logPath, $logContent);
            
            // Also log to Magento system log
            $missingNames = array_column($missingExtensions, 'name');
            $this->logger->warning(
                __('LLMs.txt Generator: Missing extensions detected: %1. See %2 for details.', 
                    implode(', ', $missingNames), 
                    $logFilename
                )
            );
        } catch (FileSystemException $e) {
            $this->logger->error(
                __('Failed to generate extension log file: %1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->logger->error(
                __('Error while checking extensions: %1', $e->getMessage())
            );
        }
    }
}

