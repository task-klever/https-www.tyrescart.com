<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BlogFields implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'short_content', 'label' => __('Short Content')],
            ['value' => 'content', 'label' => __('Content')],
            ['value' => 'meta_title', 'label' => __('Meta Title')],
            ['value' => 'meta_keywords', 'label' => __('Meta Keywords')],
            ['value' => 'meta_description', 'label' => __('Meta Description')],
        ];
    }
}

