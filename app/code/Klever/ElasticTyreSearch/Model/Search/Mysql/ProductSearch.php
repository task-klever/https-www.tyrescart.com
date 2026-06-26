<?php
namespace Klever\ElasticTyreSearch\Model\Search\Mysql;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

class ProductSearch
{
    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var Status */
    private $productStatus;

    /** @var Visibility */
    private $productVisibility;

    public function __construct(
        CollectionFactory $collectionFactory,
        Status $productStatus,
        Visibility $productVisibility
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productStatus     = $productStatus;
        $this->productVisibility = $productVisibility;
    }

    /**
     * Search products via MySQL. Returns array of product IDs.
     */
    public function search(string $query, int $limit = 5): array
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addAttributeToFilter('name', ['like' => '%' . $query . '%'])
            ->setPageSize($limit);

        return $collection->getAllIds();
    }
}
