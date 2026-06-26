<?php
namespace Klever\ElasticTyreSearch\Model\Search\Elastic;

use Smile\ElasticsuiteCore\Search\Request\Builder;
use Magento\Search\Model\SearchEngine;
use Magento\Store\Model\StoreManagerInterface;

class BlogSearch
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
     * Search blog posts via ElasticSuite. Returns array of post documents.
     */
    public function search(string $query, int $limit = 3): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $request  = $this->requestBuilder->create(
            $storeId,
            'klever_blog_search',
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
            $results[] = [
                'id'    => (int) $document->getId(),
                'title' => (string) ($data['title'] ?? ''),
                'url'   => '/blog/' . ($data['url_key'] ?? ''),
                'image' => (string) ($data['image'] ?? ''),
            ];
        }

        return $results;
    }
}
