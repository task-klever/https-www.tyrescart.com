<?php
namespace Klever\ElasticTyreSearch\Model\Search\Mysql;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class CmsSearch
{
    /** @var CollectionFactory */
    private $pageCollectionFactory;

    public function __construct(CollectionFactory $pageCollectionFactory)
    {
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * Search CMS pages via MySQL LIKE. Returns array of page documents.
     */
    public function search(string $query, int $limit = 2): array
    {
        $collection = $this->pageCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter(
                ['title', 'content'],
                [['like' => '%' . $query . '%'], ['like' => '%' . $query . '%']]
            )
            ->setPageSize($limit);

        $results = [];
        foreach ($collection as $page) {
            $results[] = [
                'id'    => (int) $page->getId(),
                'title' => (string) $page->getTitle(),
                'url'   => '/' . $page->getIdentifier(),
            ];
        }

        return $results;
    }
}
