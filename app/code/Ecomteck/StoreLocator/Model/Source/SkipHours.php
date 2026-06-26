<?php

namespace Ecomteck\StoreLocator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SkipHours implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['value' => '', 'label' => __('-- No Skip Hours --')];
        for ($i = 1; $i <= 24; $i++) {
            $label = $i === 1 ? __('1 Hour') : __('%1 Hours', $i);
            $options[] = ['value' => $i, 'label' => $label];
        }
        return $options;
    }
}
