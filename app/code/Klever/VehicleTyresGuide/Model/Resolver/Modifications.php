<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Model\Resolver;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Modifications implements ResolverInterface
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
        if (empty($args['year'])) {
            throw new GraphQlInputException(__('Required parameter "year" is missing.'));
        }

        $result = $this->resource->getModifications(
            trim($args['make']),
            trim($args['model']),
            (int)$args['year']
        );

        $fuels  = [];
        $hpVals = [];
        $data   = [];

        foreach ($result['rows'] as $r) {
            if (!empty($r['fuel'])) {
                $fuels[] = strtolower((string)$r['fuel']);
            }
            if (!empty($r['power_hp'])) {
                $hpVals[] = (int)$r['power_hp'];
            }

            $data[] = [
                'slug'       => $r['modification_slug'] ?? null,
                'name'       => $r['modification_name'] ?? null,
                'trim'       => $r['trim']              ?? null,
                'start_year' => isset($r['start_year']) ? (int)$r['start_year'] : null,
                'end_year'   => isset($r['end_year'])   ? (int)$r['end_year']   : null,
                'is_stock'   => isset($r['is_stock'])   ? (bool)$r['is_stock']  : null,
                'make'  => ['slug' => $r['make_slug']  ?? null, 'name' => $r['make_name']  ?? null],
                'model' => ['slug' => $r['model_slug'] ?? null, 'name' => $r['model_name'] ?? null],
                'engine' => [
                    'code'     => $r['engine_code'] ?? null,
                    'fuel'     => $r['fuel']        ?? null,
                    'capacity' => isset($r['capacity']) ? (string)$r['capacity'] : null,
                    'type'     => $r['type']        ?? null,
                    'drive'    => $r['drive']       ?? null,
                    'power_hp' => isset($r['power_hp']) ? (int)$r['power_hp'] : null,
                    'power_kw' => isset($r['power_kw']) ? (int)$r['power_kw'] : null,
                ],
                'front_wheel' => [
                    'rim'              => $r['front_rim']              ?? null,
                    'rim_diameter'     => isset($r['front_rim_diameter'])  ? (int)$r['front_rim_diameter']   : null,
                    'rim_width'        => isset($r['front_rim_width'])     ? (float)$r['front_rim_width']    : null,
                    'rim_offset'       => isset($r['front_rim_offset'])    ? (int)$r['front_rim_offset']     : null,
                    'tire_full'        => $r['front_tire_full']        ?? null,
                    'tire'             => $r['front_tire']             ?? null,
                    'tire_width'       => isset($r['front_tire_width'])      ? (int)$r['front_tire_width']      : null,
                    'tire_aspect_ratio'=> isset($r['front_tire_aspect_ratio']) ? (int)$r['front_tire_aspect_ratio'] : null,
                    'tire_construction'=> $r['front_tire_construction'] ?? null,
                    'load_index'       => isset($r['front_load_index']) ? (int)$r['front_load_index'] : null,
                    'speed_index'      => $r['front_speed_index']      ?? null,
                    'pressure_bar'     => isset($r['front_pressure_bar']) ? (float)$r['front_pressure_bar'] : null,
                    'pressure_psi'     => isset($r['front_pressure_psi']) ? (string)$r['front_pressure_psi'] : null,
                    'pressure_kpa'     => isset($r['front_pressure_kpa']) ? (string)$r['front_pressure_kpa'] : null,
                    'tire_width_mm'    => isset($r['front_tire_width_mm'])    ? (int)$r['front_tire_width_mm']    : null,
                    'tire_diameter_mm' => isset($r['front_tire_diameter_mm']) ? (int)$r['front_tire_diameter_mm'] : null,
                    'tire_weight_kg'   => isset($r['front_tire_weight_kg'])   ? (string)$r['front_tire_weight_kg'] : null,
                ],
                'rear_wheel' => [
                    'rim'              => $r['rear_rim']              ?? null,
                    'rim_diameter'     => isset($r['rear_rim_diameter'])  ? (int)$r['rear_rim_diameter']   : null,
                    'rim_width'        => isset($r['rear_rim_width'])     ? (float)$r['rear_rim_width']    : null,
                    'rim_offset'       => isset($r['rear_rim_offset'])    ? (int)$r['rear_rim_offset']     : null,
                    'tire_full'        => $r['rear_tire_full']        ?? null,
                    'tire'             => $r['rear_tire']             ?? null,
                    'tire_width'       => isset($r['rear_tire_width'])       ? (int)$r['rear_tire_width']       : null,
                    'tire_aspect_ratio'=> isset($r['rear_tire_aspect_ratio'])  ? (int)$r['rear_tire_aspect_ratio']  : null,
                    'tire_construction'=> $r['rear_tire_construction']  ?? null,
                    'load_index'       => isset($r['rear_load_index'])  ? (int)$r['rear_load_index']  : null,
                    'speed_index'      => $r['rear_speed_index']       ?? null,
                    'pressure_bar'     => isset($r['rear_pressure_bar'])  ? (float)$r['rear_pressure_bar']  : null,
                    'pressure_psi'     => isset($r['rear_pressure_psi'])  ? (string)$r['rear_pressure_psi']  : null,
                    'pressure_kpa'     => isset($r['rear_pressure_kpa'])  ? (string)$r['rear_pressure_kpa']  : null,
                    'tire_width_mm'    => isset($r['rear_tire_width_mm'])    ? (int)$r['rear_tire_width_mm']    : null,
                    'tire_diameter_mm' => isset($r['rear_tire_diameter_mm']) ? (int)$r['rear_tire_diameter_mm'] : null,
                    'tire_weight_kg'   => isset($r['rear_tire_weight_kg'])   ? (string)$r['rear_tire_weight_kg']  : null,
                ],
                'fitment' => [
                    'bolt_pattern'      => $r['bolt_pattern']               ?? null,
                    'pcd'               => isset($r['pcd'])                 ? (int)$r['pcd']               : null,
                    'stud_holes'        => isset($r['stud_holes'])          ? (int)$r['stud_holes']         : null,
                    'centre_bore'       => isset($r['centre_bore'])         ? (string)$r['centre_bore']     : null,
                    'thread_size'       => $r['thread_size']                ?? null,
                    'fasteners_type'    => $r['wheel_fasteners_type']       ?? null,
                    'tightening_torque' => $r['wheel_tightening_torque']    ?? null,
                    'rear_bolt_pattern' => $r['rear_axis_bolt_pattern']     ?? null,
                    'rear_pcd'          => isset($r['rear_axis_pcd'])       ? (int)$r['rear_axis_pcd']      : null,
                    'rear_stud_holes'   => isset($r['rear_axis_stud_holes']) ? (int)$r['rear_axis_stud_holes'] : null,
                    'rear_centre_bore'  => isset($r['rear_axis_centre_bore']) ? (string)$r['rear_axis_centre_bore'] : null,
                ],
            ];
        }

        $fuels = array_values(array_unique($fuels));
        sort($fuels);

        return [
            'data' => $data,
            'meta' => [
                'count'  => count($data),
                'fuels'  => $fuels,
                'hp_min' => $hpVals ? min($hpVals) : null,
                'hp_max' => $hpVals ? max($hpVals) : null,
            ],
        ];
    }
}
