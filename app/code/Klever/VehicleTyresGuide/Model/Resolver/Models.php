<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Model\Resolver;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

class Models implements ResolverInterface
{
    public function __construct(
        private readonly FlatWheelData        $resource,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        if (empty($args['make'])) {
            throw new GraphQlInputException(__('Required parameter "make" is missing.'));
        }

        $params = [
            'region'   => $args['region']
                ?? $this->scopeConfig->getValue('klever_vehicle/general/default_region', ScopeInterface::SCOPE_STORE)
                ?: null,
            'ordering' => $args['ordering'] ?? 'slug',
            'limit'    => isset($args['limit']) ? max(1, min(500, (int)$args['limit'])) : null,
            'offset'   => isset($args['offset']) ? max(0, (int)$args['offset']) : 0,
        ];

        $result = $this->resource->getModels(trim($args['make']), $params);

        return [
            'data' => $result['rows'],
            'meta' => ['count' => $result['total']],
        ];
    }
}
