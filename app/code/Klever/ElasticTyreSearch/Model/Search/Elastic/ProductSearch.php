<?php
namespace Klever\ElasticTyreSearch\Model\Search\Elastic;

use Smile\ElasticsuiteCore\Search\Request\Builder;
use Magento\Search\Model\SearchEngine;
use Magento\Store\Model\StoreManagerInterface;

class ProductSearch
{
    /** @var Builder */
    private $requestBuilder;

    /** @var SearchEngine */
    private $searchEngine;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        Builder $requestBuilder,
        SearchEngine $searchEngine,
        StoreManagerInterface $storeManager
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->searchEngine   = $searchEngine;
        $this->storeManager   = $storeManager;
    }

    /**
     * Search products via ElasticSuite. Returns array of product data from ES _source.
     */
    public function search(string $query, int $limit = 5): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $request  = $this->requestBuilder->create(
            $storeId,
            'klever_product_search',
            0,
            $limit,
            $query,
            [],
            []
        );

        $response = $this->searchEngine->search($request);

        $products = [];
        foreach ($response as $document) {
            $source = $document->getSource() ?? [];

            // Price is stored as an array of per-group objects; use group 0 (guest)
            $price = 0.0;
            foreach ((array) ($source['price'] ?? []) as $priceGroup) {
                if (isset($priceGroup['customer_group_id']) && (int) $priceGroup['customer_group_id'] === 0) {
                    $price = (float) ($priceGroup['final_price'] ?? 0);
                    break;
                }
            }
            if ($price === 0.0 && !empty($source['price'])) {
                $first = reset($source['price']);
                $price = (float) ($first['final_price'] ?? 0);
            }

            $products[] = [
                'id'      => (int) $document->getId(),
                'name'    => $this->scalar($source['name'] ?? ''),
                'url_key' => $this->scalar($source['url_key'] ?? ''),
                'image'   => $this->scalar($source['image'] ?? ''),
                'price'   => $price,
                'brand'   => $this->scalar($source['option_text_mgs_brand'] ?? ''),
            ];
        }

        return $products;
    }

    /** ES source fields can come back as single-element arrays; normalise to string. */
    private function scalar($value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }
        return (string) ($value ?? '');
    }
}
