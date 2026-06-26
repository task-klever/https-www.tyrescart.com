<?php
namespace Klever\ElasticTyreSearch\Model\Search\Mysql;

use MGS\Brand\Model\Resource\Brand\CollectionFactory;

class BrandSearch
{
    /** @var CollectionFactory */
    private $brandCollectionFactory;

    public function __construct(CollectionFactory $brandCollectionFactory)
    {
        $this->brandCollectionFactory = $brandCollectionFactory;
    }

    /**
     * Search brands via MySQL LIKE. Returns array of brand documents.
     */
    public function search(string $query, int $limit = 4): array
    {
        $collection = $this->brandCollectionFactory->create()
            ->addFieldToFilter('name', ['like' => '%' . $query . '%'])
            ->setPageSize($limit);

        $results = [];
        foreach ($collection as $brand) {
            $results[] = [
                'id'    => (int) $brand->getId(),
                'name'  => (string) $brand->getName(),
                'url'   => '/brand/' . $brand->getUrlKey(),
                'image' => (string) $brand->getImage(),
            ];
        }

        return $results;
    }
}
