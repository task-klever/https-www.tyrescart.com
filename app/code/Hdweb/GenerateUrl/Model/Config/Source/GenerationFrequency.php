<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GenerationFrequency implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'on_demand', 'label' => __('On Demand')],
            ['value' => 'custom_cron', 'label' => __('Custom Cron')],
            ['value' => 'hourly', 'label' => __('Hourly')],
            ['value' => 'daily', 'label' => __('Daily')],
        ];
    }
}

