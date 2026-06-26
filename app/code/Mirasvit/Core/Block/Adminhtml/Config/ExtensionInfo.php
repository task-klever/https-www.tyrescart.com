<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-core
 * @version   1.4.43
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Core\Block\Adminhtml\Config;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Mirasvit\Core\Model\Package;
use Mirasvit\Core\Service\FeatureService;
use Mirasvit\Core\Service\PackageService;

class ExtensionInfo extends Template implements RendererInterface
{
    protected $_template  = 'Mirasvit_Core::config/extension-info.phtml';

    private   $moduleName = '';

    private   $packageService;

    private   $featureService;

    public function __construct(
        PackageService   $packageService,
        FeatureService   $featureService,
        Template\Context $context,
        array            $data = []
    ) {
        $this->packageService = $packageService;
        $this->featureService = $featureService;

        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element): string
    {
        $this->moduleName = (string)$element->getDataByPath('group/module_name');
        if (!$this->moduleName) {
            return '';
        }

        return (string)$this->toHtml();
    }

    public function getPackage(): ?Package
    {
        return $this->packageService->getPackage($this->moduleName);
    }

    public function getRequestUrl(): string
    {
        return $this->featureService->getFeatureRequestUrl($this->moduleName);
    }
}