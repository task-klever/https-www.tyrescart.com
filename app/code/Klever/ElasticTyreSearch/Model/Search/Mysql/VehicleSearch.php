<?php
namespace Klever\ElasticTyreSearch\Model\Search\Mysql;

use Hdweb\Vehicles\Model\ResourceModel\Vehicles\CollectionFactory;

class VehicleSearch
{
    /** @var CollectionFactory */
    private $vehicleCollectionFactory;

    public function __construct(CollectionFactory $vehicleCollectionFactory)
    {
        $this->vehicleCollectionFactory = $vehicleCollectionFactory;
    }

    /**
     * Search vehicles via MySQL LIKE. Returns array of vehicle documents.
     */
    public function search(string $query, int $limit = 4): array
    {
        $collection = $this->vehicleCollectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter(
                ['make', 'model'],
                [['like' => '%' . $query . '%'], ['like' => '%' . $query . '%']]
            )
            ->setPageSize($limit);

        $results = [];
        $seen    = [];
        foreach ($collection as $vehicle) {
            $make  = trim((string) $vehicle->getMake());
            $model = trim((string) $vehicle->getModel());
            $key   = strtolower($make . '|' . $model);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $makeSlug   = strtolower(str_replace(' ', '-', $make));
            $modelSlug  = strtolower(str_replace(' ', '-', $model));
            $results[]  = [
                'id'    => (int) $vehicle->getId(),
                'make'  => $make,
                'model' => $model,
                'url'   => '/tyres/cars/' . $makeSlug . ($modelSlug ? '/' . $modelSlug : ''),
            ];
        }

        return $results;
    }
}
