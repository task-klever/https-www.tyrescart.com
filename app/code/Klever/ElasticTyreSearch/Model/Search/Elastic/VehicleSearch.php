<?php
namespace Klever\ElasticTyreSearch\Model\Search\Elastic;

use Smile\ElasticsuiteCore\Search\Request\Builder;
use Magento\Search\Model\SearchEngine;
use Magento\Store\Model\StoreManagerInterface;

class VehicleSearch
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
     * Search vehicle makes via ElasticSuite. Returns array of make documents.
     */
    public function search(string $query, int $limit = 4): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $request  = $this->requestBuilder->create(
            $storeId,
            'klever_vehicle_search',
            0,
            $limit,
            $query,
            [],
            []
        );

        $response = $this->searchEngine->search($request);

        $results = [];
        foreach ($response as $document) {
            $data      = $document->getSource() ?? [];
            $slug      = (string) ($data['slug'] ?? '');
            $results[] = [
                'id'    => (int) $document->getId(),
                'name'  => (string) ($data['name'] ?? ''),
                'url'   => '/tyres/cars/' . $slug,
                'image' => (string) ($data['logo_image'] ?? ''),
            ];
        }

        return $results;
    }
}
