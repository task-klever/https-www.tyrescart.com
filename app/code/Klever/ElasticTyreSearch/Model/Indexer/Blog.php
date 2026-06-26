<?php
namespace Klever\ElasticTyreSearch\Model\Indexer;

use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Klever\ElasticTyreSearch\Model\Indexer\Datasource\Blog as BlogDatasource;

class Blog implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEXER_ID = 'klever_blog_fulltext';
    const INDEX_NAME = 'klever_blog';
    const TYPE_NAME  = 'post';

    /** @var IndexerInterface */
    private $indexerHandler;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var DimensionFactory */
    private $dimensionFactory;

    /** @var BlogDatasource */
    private $datasource;

    public function __construct(
        IndexerInterface $indexerHandler,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        BlogDatasource $datasource
    ) {
        $this->indexerHandler   = $indexerHandler;
        $this->storeManager     = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->datasource       = $datasource;
    }

    public function execute($ids)
    {
        foreach (array_keys($this->storeManager->getStores()) as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $documents = $this->datasource->addData($storeId, []);
            $filtered  = array_filter($documents, fn($doc) => in_array($doc['post_id'], $ids));
            if (!empty($filtered)) {
                $this->indexerHandler->saveIndex([$dimension], new \ArrayIterator($filtered));
            }
        }
    }

    public function executeFull()
    {
        foreach (array_keys($this->storeManager->getStores()) as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $this->indexerHandler->cleanIndex([$dimension]);
            $documents = $this->datasource->addData($storeId, []);
            $this->indexerHandler->saveIndex([$dimension], new \ArrayIterator($documents));
        }
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
