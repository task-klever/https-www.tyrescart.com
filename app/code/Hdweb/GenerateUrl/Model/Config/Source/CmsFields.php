<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CmsFields implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'title', 'label' => __('Title')],
            ['value' => 'meta_title', 'label' => __('Meta Title')],
            ['value' => 'meta_description', 'label' => __('Meta Description')],
            ['value' => 'meta_keywords', 'label' => __('Meta Keywords')],
            ['value' => 'content', 'label' => __('Content')],
        ];
    }
}

