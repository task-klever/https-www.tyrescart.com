<?php

namespace WeltPixel\GA4\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ItemVariant
 *
 * @package WeltPixel\GA4\Model\Config\Source
 */
class ItemVariant implements ArrayInterface
{

    const CONFIGURATION_COMBINATION = 'configuration_combination';
    const CHILD_SKU = 'child_sku';

    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::CONFIGURATION_COMBINATION,
                'label' => __('Configuration Combination')
            ),
            array(
                'value' => self::CHILD_SKU,
                'label' => __('Child SKU')
            )
        );
    }
}
