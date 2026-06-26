<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductFields implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'page_name', 'label' => __('Page Name')],
            ['value' => 'short_description', 'label' => __('Short Description')],
            ['value' => 'sku', 'label' => __('SKU')],
            ['value' => 'meta_title', 'label' => __('Meta Title')],
            ['value' => 'meta_keywords', 'label' => __('Meta Keywords')],
            ['value' => 'description', 'label' => __('Description')],
            ['value' => 'price', 'label' => __('Price')],
        ];
    }
}

