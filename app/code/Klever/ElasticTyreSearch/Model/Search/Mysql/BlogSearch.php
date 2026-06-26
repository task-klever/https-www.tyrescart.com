<?php
namespace Klever\ElasticTyreSearch\Model\Search\Mysql;

use MGS\Blog\Model\Resource\Post\CollectionFactory;

class BlogSearch
{
    /** @var CollectionFactory */
    private $postCollectionFactory;

    public function __construct(CollectionFactory $postCollectionFactory)
    {
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * Search blog posts via MySQL LIKE. Returns array of post documents.
     */
    public function search(string $query, int $limit = 3): array
    {
        $collection = $this->postCollectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter(
                ['title', 'short_content'],
                [['like' => '%' . $query . '%'], ['like' => '%' . $query . '%']]
            )
            ->setPageSize($limit);

        $results = [];
        foreach ($collection as $post) {
            $results[] = [
                'id'    => (int) $post->getId(),
                'title' => (string) $post->getTitle(),
                'url'   => '/blog/' . $post->getUrlKey(),
                'image' => (string) $post->getImage(),
            ];
        }

        return $results;
    }
}
