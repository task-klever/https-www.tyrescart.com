<?php

namespace MGS\Brand\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class PartsCategories implements ArrayInterface
{
    //const YES = 1;
    //const NO = 0;

    public function toOptionArray()
    {
        $options = [
            'air_filter' => __('Air Filter'),
            'battery' => __('Battery'),
            'cabin_filter' => __('Cabin Filter'),
            'brake_discs' => __('Brake Discs'),
            'brake_pads' => __('Brake Pads'),
            'lubicants' => __('Lubricants'),
            'oil_filter' => __('Oil Filter'),
            'sensor' => __('Sensor'),
            'tyres' => __('Tyres'),
            'wheels' => __('Wheels'),
            'service_package' => __('Service Package'),
            'spark_plug' => __('Spark Plug'),
            'wheel_protector' => __('Wheel Protector')

        ];
        return $options;
    }
}
