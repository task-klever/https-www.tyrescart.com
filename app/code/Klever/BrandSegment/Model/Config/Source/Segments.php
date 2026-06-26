<?php
declare(strict_types=1);

namespace Klever\BrandSegment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Segments implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'top_premium', 'label' => __('Top Premium')],
            ['value' => 'top_quality', 'label' => __('Top Quality')],
            ['value' => 'top_budget',  'label' => __('Top Budget')],
        ];
    }
}
