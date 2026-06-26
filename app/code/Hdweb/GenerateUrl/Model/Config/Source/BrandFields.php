<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BrandFields implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'description', 'label' => __('Description')],
            ['value' => 'meta_title', 'label' => __('Meta Title')],
            ['value' => 'meta_keywords', 'label' => __('Meta Keywords')],
            ['value' => 'meta_description', 'label' => __('Meta Description')],
        ];
    }
}

