<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Driver\File;

class Version extends Field
{
    public function __construct(
        Context $context,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly File $fileDriver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return '<span class="klever-module-version">' . $this->escapeHtml($this->getVersion()) . '</span>';
    }

    private function getVersion(): string
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Klever_VehicleTyresGuide');
        if (!$path) {
            return 'unknown';
        }
        try {
            if (!$this->fileDriver->isExists($path . '/composer.json')) {
                return 'unknown';
            }
            $data = json_decode($this->fileDriver->fileGetContents($path . '/composer.json'), true);
        } catch (\Throwable) {
            return 'unknown';
        }
        return (string)($data['version'] ?? 'unknown');
    }
}
