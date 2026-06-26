<?php
namespace Klever\ElasticTyreSearch\Model;

use Klever\ElasticTyreSearch\Model\Search\Elastic;
use Klever\ElasticTyreSearch\Plugin\NormalizeTyreSizeQuery;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class GlobalSearchService
{
    /** @var Elastic\ProductSearch */
    private $productSearch;

    /** @var Elastic\BrandSearch */
    private $brandSearch;

    /** @var Elastic\BlogSearch */
    private $blogSearch;

    /** @var Elastic\VehicleSearch */
    private $vehicleSearch;

    /** @var Elastic\CmsSearch */
    private $cmsSearch;

    /** @var Elastic\TyreSizeSearch */
    private $tyreSizeSearch;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var TyreSizeEnrichmentService */
    private $enrichmentService;

    public function __construct(
        Elastic\ProductSearch       $productSearch,
        Elastic\BrandSearch         $brandSearch,
        Elastic\BlogSearch          $blogSearch,
        Elastic\VehicleSearch       $vehicleSearch,
        Elastic\CmsSearch           $cmsSearch,
        Elastic\TyreSizeSearch      $tyreSizeSearch,
        StoreManagerInterface       $storeManager,
        TyreSizeEnrichmentService   $enrichmentService
    ) {
        $this->productSearch      = $productSearch;
        $this->brandSearch        = $brandSearch;
        $this->blogSearch         = $blogSearch;
        $this->vehicleSearch      = $vehicleSearch;
        $this->cmsSearch          = $cmsSearch;
        $this->tyreSizeSearch     = $tyreSizeSearch;
        $this->storeManager       = $storeManager;
        $this->enrichmentService  = $enrichmentService;
    }

    /**
     * Run global search across all content types.
     */
    public function search(string $query, int $limit = 5): array
    {
        $store    = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $baseUrl  = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        // Products: get full data from ES _source — no DB queries
        $products = [];
        foreach ($this->productSearch->search($query, $limit) as $p) {
            $image      = $p['image'];
            $products[] = [
                'id'    => $p['id'],
                'name'  => $p['name'],
                'url'   => $baseUrl . $p['url_key'] . '.html',
                'image' => ($image && $image !== 'no_selection')
                    ? $mediaUrl . 'catalog/product' . $image
                    : '',
                'price' => $p['price'],
                'brand' => $p['brand'],
            ];
        }

        // Prefix relative images with media URL
        $brands = $this->brandSearch->search($query, 4);
        foreach ($brands as &$b) {
            if (!empty($b['image']) && strpos($b['image'], 'http') !== 0) {
                $b['image'] = $mediaUrl . $b['image'];
            }
        }

        $blogs = $this->blogSearch->search($query, 3);
        foreach ($blogs as &$bl) {
            if (!empty($bl['image']) && strpos($bl['image'], 'http') !== 0) {
                $bl['image'] = $mediaUrl . 'mgs_blog/' . $bl['image'];
            }
        }

        $vehicles = $this->vehicleSearch->search($query, 6);
        foreach ($vehicles as &$v) {
            if (!empty($v['image']) && strpos($v['image'], 'http') !== 0) {
                $v['image'] = $mediaUrl . $v['image'];
            }
        }

        // Tyre size suggestions — search on the normalised query, enriched with vehicle/product data
        $normalizedQuery = NormalizeTyreSizeQuery::normalize($query);
        $rawSizes = $this->tyreSizeSearch->search($normalizedQuery, 10);

        // // Old simple tyre size building (replaced by enriched version)
        // $tyreSizes = [];
        // foreach ($rawSizes as $size) {
        //     $tyreSizes[] = [
        //         'size' => $size,
        //         'url'  => $baseUrl . 'catalogsearch/result/?q=' . urlencode($size),
        //     ];
        // }

        $enrichment = $this->enrichmentService->enrich($rawSizes);
        $tyreSizes = [];
        foreach ($rawSizes as $size) {
            $info = $enrichment[$size] ?? [];
            $tyreSizes[] = array_merge(
                [
                    'size' => $size,
                    'url'  => $baseUrl . 'catalogsearch/result/?q=' . urlencode($size),
                ],
                $info
            );
        }

        return [
            'tyresizes' => $tyreSizes,
            'products'  => $products,
            'brands'    => $brands,
            'blogs'     => $blogs,
            'vehicles'  => $vehicles,
            'cms'       => $this->cmsSearch->search($query, 2),
        ];
    }
}
