<?php
namespace Klever\ElasticTyreSearch\Model\Indexer\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;

class Cms implements DatasourceInterface
{
    /** @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory */
    private $pageCollectionFactory;

    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData)
    {
        $collection = $this->pageCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($storeId)
            ->setPageSize(false);

        foreach ($collection as $page) {
            $id = (int) $page->getId();
            $indexData[$id] = [
                'page_id'    => $id,
                'title'      => (string) $page->getTitle(),
                'content'    => (string) strip_tags((string) $page->getContent()),
                'identifier' => (string) $page->getIdentifier(),
                'is_active'  => (int) $page->getIsActive(),
            ];
        }

        return $indexData;
    }
}
