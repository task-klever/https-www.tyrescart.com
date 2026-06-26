<?php

namespace WeltPixel\GA4\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class AdsParentVsChild
 *
 * @package WeltPixel\GA4\Model\Config\Source
 */
class AdsParentVsChild implements ArrayInterface
{

    const SAMES_AS_FOR_ANALYTICS = 'same_as_for_analytics';
    const CHILD = 'child';
    const PARENT = 'parent';

    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::SAMES_AS_FOR_ANALYTICS,
                'label' => __('Same as for Analytics')
            ),
            array(
                'value' => self::CHILD,
                'label' => __('Child')
            ),
            array(
                'value' => self::PARENT,
                'label' => __('Parent')
            )
        );
    }
}
