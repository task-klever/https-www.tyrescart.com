<?php

namespace Ecomteck\StoreLocator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CutoffTime implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['value' => '', 'label' => __('-- No Cutoff --')];
        for ($i = 0; $i <= 24; $i++) {
            $time = sprintf('%02d:00', $i);
            $options[] = ['value' => $time, 'label' => $time];
        }
        return $options;
    }
}
