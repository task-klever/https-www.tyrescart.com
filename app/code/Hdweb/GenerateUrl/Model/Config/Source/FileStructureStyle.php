<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FileStructureStyle implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'markdown', 'label' => __('Markdown Style')],
            ['value' => 'plain', 'label' => __('Plain Text')],
            ['value' => 'json', 'label' => __('JSON Format')],
        ];
    }
}

