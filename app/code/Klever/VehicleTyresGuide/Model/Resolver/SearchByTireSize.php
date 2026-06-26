<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Model\Resolver;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SearchByTireSize implements ResolverInterface
{
    public function __construct(
        private readonly FlatWheelData $resource
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        if (empty($args['width'])) {
            throw new GraphQlInputException(__('Required parameter "width" is missing.'));
        }
        if (empty($args['height'])) {
            throw new GraphQlInputException(__('Required parameter "height" is missing.'));
        }
        if (empty($args['rim'])) {
            throw new GraphQlInputException(__('Required parameter "rim" is missing.'));
        }

        $result = $this->resource->searchByTyreSize(
            (int)$args['width'],
            (int)$args['height'],
            (int)$args['rim']
        );

        $data = array_map(fn($r) => [
            'make_name'   => $r['make_name']   ?? null,
            'make_slug'   => $r['make_slug']   ?? null,
            'model_name'  => $r['model_name']  ?? null,
            'model_slug'  => $r['model_slug']  ?? null,
            'year_ranges' => $r['year_ranges'] ?? null,
            'engine_code' => $r['engine_code'] ?? null,
            'is_stock'    => isset($r['is_stock']) ? (bool)$r['is_stock'] : null,
            'front_width' => isset($r['front_width']) ? (int)$r['front_width'] : null,
            'front_height'=> isset($r['front_height']) ? (int)$r['front_height'] : null,
            'front_rim'   => isset($r['front_rim'])   ? (int)$r['front_rim']   : null,
            'rear_width'  => isset($r['rear_width'])  ? (int)$r['rear_width']  : null,
            'rear_height' => isset($r['rear_height']) ? (int)$r['rear_height'] : null,
            'rear_rim'    => isset($r['rear_rim'])    ? (int)$r['rear_rim']    : null,
        ], $result['rows']);

        $found = count($data) > 0;

        return [
            'status'  => 'success',
            'message' => $found ? 'Tyre size matches found.' : 'No matching tyre size found.',
            'data'    => $data,
        ];
    }
}
