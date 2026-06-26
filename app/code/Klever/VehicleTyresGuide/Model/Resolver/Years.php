<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Model\Resolver;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Years implements ResolverInterface
{
    public function __construct(
        private readonly FlatWheelData $resource
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        if (empty($args['make'])) {
            throw new GraphQlInputException(__('Required parameter "make" is missing.'));
        }
        if (empty($args['model'])) {
            throw new GraphQlInputException(__('Required parameter "model" is missing.'));
        }

        $result = $this->resource->getYears(trim($args['make']), trim($args['model']));

        $data = array_map(fn(int $y) => ['slug' => $y, 'name' => $y], $result['years']);

        return [
            'data' => $data,
            'meta' => ['count' => count($data)],
        ];
    }
}
