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
 * @package   mirasvit/module-report-builder
 * @version   1.1.8
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\ReportBuilder\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\AuthorizationInterface;

class BuilderJs extends Template
{
    protected $_template = 'Mirasvit_ReportBuilder::builder_js.phtml';

    private   $urlBuilder;

    private   $serializer;

    private   $authorization;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->urlBuilder    = $context->getUrlBuilder();
        $this->serializer    = $serializer;
        $this->authorization = $authorization;

        parent::__construct($context, $data);
    }

    public function getConfig(): array
    {
        return [];
    }

    public function jsonEncode($data)
    {
        return $this->serializer->serialize($data);
    }

    public function getDuplicateUrl(): string
    {
        return $this->urlBuilder->getUrl('reportBuilder/api/duplicate');
    }

    public function getSaveUrl(): string
    {
        return $this->urlBuilder->getUrl('reportBuilder/api/save');
    }

    public function getDeleteUrl(): string
    {
        return $this->urlBuilder->getUrl('reportBuilder/api/delete');
    }

    public function isSaveAllowed(): bool
    {
        return $this->authorization->isAllowed('Mirasvit_ReportBuilder::save');
    }

    public function isShareAllowed(): bool
    {
        return $this->authorization->isAllowed('Mirasvit_ReportBuilder::share');
    }
}
