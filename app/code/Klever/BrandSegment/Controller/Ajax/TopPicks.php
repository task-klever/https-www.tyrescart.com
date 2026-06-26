<?php
declare(strict_types=1);

namespace Klever\BrandSegment\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TopPicks extends Action
{
    private JsonFactory $jsonFactory;
    private ResourceConnection $resource;
    private ProductCollectionFactory $productCollectionFactory;
    private EavConfig $eavConfig;
    private StoreManagerInterface $storeManager;
    private ImageHelper $imageHelper;
    private ScopeConfigInterface $scopeConfig;

    private const SEGMENT_LABELS = [
        'top_premium' => 'Top Premium',
        'top_quality' => 'Top Quality',
        'top_budget'  => 'Top Budget',
    ];

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ResourceConnection $resource,
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        ImageHelper $imageHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resource = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        $enabled = $this->scopeConfig->isSetFlag('klever_brandsegment/general/enabled');
        if (!$enabled) {
            return $result->setData(['segments' => []]);
        }

        $width = $this->getRequest()->getParam('width', '');
        $height = $this->getRequest()->getParam('height', '');
        $rim = $this->getRequest()->getParam('rim', '');
        $limit = (int)($this->getRequest()->getParam('limit') ?: $this->scopeConfig->getValue('klever_brandsegment/general/products_per_segment') ?: 3);

        $hasSizeFilter = ($width && $rim);

        // Resolve option IDs when size params are present
        $widthOptionId = null;
        $rimOptionId = null;
        $heightOptionId = null;
        if ($hasSizeFilter) {
            $widthOptionId = $this->getOptionId('width', $width);
            $rimOptionId = $this->getOptionId('rim', $rim);
            $heightOptionId = $height ? $this->getOptionId('height', $height) : null;

            if (!$widthOptionId || !$rimOptionId) {
                return $result->setData(['segments' => []]);
            }
        }

        // Load brand segment data
        $conn = $this->resource->getConnection();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $allowedSegments = $this->scopeConfig->getValue('klever_brandsegment/general/segments');
        $segmentList = $allowedSegments ? explode(',', $allowedSegments) : array_keys(self::SEGMENT_LABELS);

        $brandRows = $conn->fetchAll(
            'SELECT brand_id, name, option_id, small_image, brand_segment FROM mgs_brand WHERE status = 1 AND brand_segment IN ("' . implode('","', $segmentList) . '") AND option_id > 0'
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
            return $result->setData(['segments' => []]);
        }

        // Build product collection
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]])
            ->addAttributeToFilter('brand', ['in' => array_keys($brandOptionToSegment)]);

        // Filter by tyre size when params are present
        if ($hasSizeFilter) {
            $collection->addAttributeToFilter('width', $widthOptionId);
            $collection->addAttributeToFilter('rim', $rimOptionId);
            if ($heightOptionId) {
                $collection->addAttributeToFilter('height', $heightOptionId);
            }
        } else {
            // Without size filter: only show tyres (parts_category = Tyres)
            $tyresOptionId = $this->getOptionId('parts_category', 'Tyres');
            if ($tyresOptionId) {
                $collection->addAttributeToFilter('parts_category', $tyresOptionId);
            }
        }

        $collection->setOrder('price', 'DESC');
        $collection->setPageSize(100);

        // Group by segment — 1 product per brand (cheapest from each brand)
        $segmentProducts = [];
        $segmentSeenBrands = [];
        foreach ($segmentList as $s) { $segmentProducts[$s] = []; $segmentSeenBrands[$s] = []; }

        foreach ($collection as $product) {
            $brandOptId = $product->getData('brand');
            if (!isset($brandOptionToSegment[$brandOptId])) continue;
            $seg = $brandOptionToSegment[$brandOptId];
            if (count($segmentProducts[$seg]) >= $limit) continue;
            if (in_array($brandOptId, $segmentSeenBrands[$seg])) continue;
            $segmentSeenBrands[$seg][] = $brandOptId;

            $finalPrice = (float)$product->getPriceInfo()->getPrice('final_price')->getValue();
            $regularPrice = (float)$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
            $imageUrl = $this->imageHelper->init($product, 'category_page_grid')->getUrl();
            $tyreSize = $product->getAttributeText('tyre_size') ?: '';
            $loadIndex = $product->getLoadIndex() ?: '';
            $pattern = $product->getPattern() ? ($product->getAttributeText('pattern') ?: '') : '';
            $oemMarking = $product->getOemMarking() ? ($product->getAttributeText('oem_marking') ?: '') : '';
            $runflat = $product->getRunflat() ? ($product->getAttributeText('runflat') ?: '') : '';
            $ev = $product->getResource()->getAttribute('ev') ? ($product->getAttributeText('ev') ?: '') : '';
            $year = $product->getYear() ? ($product->getAttributeText('year') ?: '') : '';
            $origin = $product->getCountry() ? ($product->getAttributeText('country') ?: '') : '';

            // Stock qty
            $listingHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Hdweb\Tyrefinder\Helper\Productlisting::class);
            $prodQty = $listingHelper->getProductAvailableQty($product->getSku());
            $stockQty = $prodQty >= 8 ? 8 : (int)$prodQty;
            $isSaleable = $stockQty > 0 && $product->isSaleable();

            // Set prices
            $set2price = number_format($finalPrice * 2, 2);
            $set4price = number_format($finalPrice * 4, 2);

            // Offer/discount
            $isOffer = 'No';
            $discountAmt = '';
            $discountQtyStep = '';
            $offerRule = $listingHelper->isAnyRuleExist($product);
            if (!empty($offerRule) && !empty($offerRule['rule_id']) && !empty($offerRule['offer_price'])) {
                $isOffer = 'Yes';
                $discountAmt = $offerRule['discount_amount'];
                $discountQtyStep = $offerRule['discount_qty_step'];
            }

            $segmentProducts[$seg][] = [
                'product_id'     => (int)$product->getId(),
                'name'           => $product->getName(),
                'url'            => $product->getProductUrl(),
                'image_url'      => $imageUrl,
                'final_price'    => $finalPrice,
                'regular_price'  => $regularPrice,
                'formatted_price' => number_format($finalPrice, 2),
                'set2price'      => $set2price,
                'set4price'      => $set4price,
                'brand_name'     => $brandOptionToName[$brandOptId] ?? '',
                'brand_logo_url' => $brandOptionToImage[$brandOptId] ?? '',
                'size'           => $tyreSize,
                'load_index'     => $loadIndex,
                'pattern'        => $pattern,
                'oem_marking'    => $oemMarking,
                'runflat'        => $runflat,
                'ev'             => ($ev && strtolower($ev) !== 'no') ? 'EV' : '',
                'year'           => $year,
                'origin'         => $origin,
                'stock_qty'      => range(1, max(1, $stockQty)),
                'is_saleable'    => $isSaleable,
                'is_offer'       => $isOffer,
                'discount_amount' => $discountAmt,
                'discount_qty'   => $discountQtyStep,
            ];
        }

        // Build response
        $segments = [];
        foreach (self::SEGMENT_LABELS as $key => $label) {
            if (!empty($segmentProducts[$key])) {
                $segments[] = [
                    'key'      => $key,
                    'label'    => (string)__($label),
                    'products' => $segmentProducts[$key],
                ];
            }
        }

        return $result->setData(['segments' => $segments]);
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
