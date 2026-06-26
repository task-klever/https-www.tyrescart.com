<?php
namespace Klever\ElasticTyreSearch\Model\Indexer\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;

class Blog implements DatasourceInterface
{
    /** @var \MGS\Blog\Model\Resource\Post\CollectionFactory */
    private $postCollectionFactory;

    public function __construct(
        \MGS\Blog\Model\Resource\Post\CollectionFactory $postCollectionFactory
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData)
    {
        $collection = $this->postCollectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->setPageSize(false);

        foreach ($collection as $post) {
            $id = (int) $post->getId();
            $indexData[$id] = [
                'post_id'       => $id,
                'title'         => (string) $post->getTitle(),
                'short_content' => (string) strip_tags((string) $post->getShortContent()),
                'url_key'       => (string) $post->getUrlKey(),
                'image'         => (string) $post->getImage(),
                'status'        => (int) $post->getStatus(),
            ];
        }

        return $indexData;
    }
}
