<?php

namespace Hdweb\Purchaseorder\Model;

use Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var array
     */
    protected $loadedData;

    // @codingStandardsIgnoreStart
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $purchaseorderCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $purchaseorderCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    // @codingStandardsIgnoreEnd

    public function getData()
    {

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $blog) {
            $this->loadedData[$blog->getBlogId()] = $blog->getData();
        }
        return $this->loadedData;
    }
}