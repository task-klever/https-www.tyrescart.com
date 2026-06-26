<?php

namespace Meetanshi\OrderNumber\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Counter
 * @package Meetanshi\OrderNumber\Model\System\Config\Source
 */
class Counter implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('--- No Reset ---')],
            ['value' => 'Y-m-d', 'label' => __('Reset Every Day')],
            ['value' => 'Y-m', 'label' => __('Reset Every Month')],
            ['value' => 'Y', 'label' => __('Reset Every Year')]
        ];
    }
}
