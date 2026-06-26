<?php
declare(strict_types=1);

namespace Klever\BrandSegment\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TopPicksBySegment implements ResolverInterface
{
    private ResourceConnection $resource;
    private ProductCollectionFactory $productCollectionFactory;
    private EavConfig $eavConfig;
    private StoreManagerInterface $storeManager;
    private ImageHelper $imageHelper;
    private ScopeConfigInterface $scopeConfig;

    private const SEGMENT_CONFIG = [
        'top_premium' => 'Top Premium',
        'top_quality' => 'Top Quality',
        'top_budget'  => 'Top Budget',
    ];

    public function __construct(
        ResourceConnection $resource,
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        ImageHelper $imageHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resource = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $width = $args['width'] ?? '';
        $height = $args['height'] ?? '';
        $rim = $args['rim'] ?? '';
        $limit = (int)($args['limit'] ?? 3);

        if (!$width || !$rim) {
            return ['segments' => []];
        }

        // Check if enabled
        $enabled = $this->scopeConfig->isSetFlag('klever_brandsegment/general/enabled');
        if (!$enabled) {
            return ['segments' => []];
        }

        $configLimit = (int)$this->scopeConfig->getValue('klever_brandsegment/general/products_per_segment');
        if ($configLimit > 0) {
            $limit = $configLimit;
        }

        // Resolve attribute text values to option IDs
        $widthOptionId = $this->getOptionId('width', $width);
        $rimOptionId = $this->getOptionId('rim', $rim);
        $heightOptionId = $height ? $this->getOptionId('height', $height) : null;

        if (!$widthOptionId || !$rimOptionId) {
            return ['segments' => []];
        }

        // Load brand segment data
        $conn = $this->resource->getConnection();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $brandRows = $conn->fetchAll(
            'SELECT brand_id, name, option_id, small_image, brand_segment FROM mgs_brand WHERE status = 1 AND brand_segment IN ("top_premium","top_quality","top_budget") AND option_id > 0'
        );

        $brandOptionToSegment = [];
        $brandOptionToImage = [];
        $brandOptionToName = [];
        foreach ($brandRows as $br) {
            $brandOptionToSegment[$br['option_id']] = $br['brand_segment'];
            $brandOptionToImage[$br['option_id']] = $br['small_image'] ? $mediaUrl . $br['small_image'] : '';
            $brandOptionToName[$br['option_id']] = $br['name'];
        }

        if (empty($brandOptionToSegment)) {
            return ['segments' => []];
        }

        // Build product collection
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_BOTH,
            ]])
            ->addAttributeToFilter('width', $widthOptionId)
            ->addAttributeToFilter('rim', $rimOptionId)
            ->addAttributeToFilter('brand', ['in' => array_keys($brandOptionToSegment)]);

        if ($heightOptionId) {
            $collection->addAttributeToFilter('height', $heightOptionId);
        }

        $collection->setOrder('price', 'ASC');
        $collection->setPageSize(100);

        // Group by segment
        $segmentProducts = ['top_premium' => [], 'top_quality' => [], 'top_budget' => []];

        foreach ($collection as $product) {
            $brandOptId = $product->getData('brand');
            if (!isset($brandOptionToSegment[$brandOptId])) {
                continue;
            }
            $seg = $brandOptionToSegment[$brandOptId];
            if (count($segmentProducts[$seg]) >= $limit) {
                continue;
            }

            $finalPrice = (float)$product->getPriceInfo()->getPrice('final_price')->getValue();
            $imageUrl = $this->imageHelper->init($product, 'category_page_grid')->getUrl();
            $tyreSize = $product->getAttributeText('tyre_size') ?: '';
            $loadIndex = $product->getLoadIndex() ?: '';
            $sizeDisplay = $tyreSize . ($loadIndex ? ' ' . $loadIndex : '');

            $segmentProducts[$seg][] = [
                'name'           => $product->getName(),
                'url'            => $product->getProductUrl(),
                'image_url'      => $imageUrl,
                'price'          => $finalPrice,
                'formatted_price' => number_format($finalPrice, 2),
                'brand_name'     => $brandOptionToName[$brandOptId] ?? '',
                'brand_logo_url' => $brandOptionToImage[$brandOptId] ?? '',
                'size'           => $sizeDisplay,
            ];
        }

        // Build response
        $segments = [];
        foreach (self::SEGMENT_CONFIG as $key => $label) {
            if (!empty($segmentProducts[$key])) {
                $segments[] = [
                    'key'      => $key,
                    'label'    => $label,
                    'products' => $segmentProducts[$key],
                ];
            }
        }

        return ['segments' => $segments];
    }

    private function getOptionId(string $attributeCode, string $label): ?string
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if ((string)$option['label'] === (string)$label) {
                return $option['value'];
            }
        }
        return null;
    }
}
