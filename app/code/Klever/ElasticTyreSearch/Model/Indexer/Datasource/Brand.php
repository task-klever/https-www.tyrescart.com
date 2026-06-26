<?php
namespace Klever\ElasticTyreSearch\Model\Indexer\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;

class Brand implements DatasourceInterface
{
    /** @var \MGS\Brand\Model\Resource\Brand\CollectionFactory */
    private $brandCollectionFactory;

    public function __construct(
        \MGS\Brand\Model\Resource\Brand\CollectionFactory $brandCollectionFactory
    ) {
        $this->brandCollectionFactory = $brandCollectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData)
    {
        $collection = $this->brandCollectionFactory->create()
            ->setPageSize(false);

        foreach ($collection as $brand) {
            $id = (int) $brand->getId();
            $indexData[$id] = [
                'brand_id' => $id,
                'name'     => (string) $brand->getName(),
                'url_key'  => (string) $brand->getUrlKey(),
                'image'    => (string) $brand->getImage(),
            ];
        }

        return $indexData;
    }
}
