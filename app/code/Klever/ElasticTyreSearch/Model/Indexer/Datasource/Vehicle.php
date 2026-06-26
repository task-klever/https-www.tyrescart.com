<?php
namespace Klever\ElasticTyreSearch\Model\Indexer\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;
use Magento\Framework\App\ResourceConnection;

class Vehicle implements DatasourceInterface
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData): array
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName('flat_wheel_data');

        // $rows = $connection->fetchAll(
        //     $connection->select()
        //         ->from('klever_vehicle_makes', ['id', 'name', 'slug', 'logo_image', 'is_active'])
        //         ->where('is_active = ?', 1)
        // );

        $rows = $connection->fetchAll(
            "SELECT
                 MIN(id) AS id,
                 make_name AS name,
                 make_slug AS slug
             FROM {$table}
             WHERE make_slug IS NOT NULL AND make_slug != ''
             GROUP BY make_slug, make_name
             ORDER BY make_slug ASC"
        );

        foreach ($rows as $row) {
            $id             = (int) $row['id'];
            $indexData[$id] = [
                'id'         => $id,
                'name'       => (string) $row['name'],
                'slug'       => (string) $row['slug'],
                'logo_image' => '',
                'is_active'  => 1,
            ];
        }

        return $indexData;
    }
}
