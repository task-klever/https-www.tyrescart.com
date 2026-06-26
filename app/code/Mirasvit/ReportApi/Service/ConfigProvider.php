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
 * @package   mirasvit/module-report-api
 * @version   1.0.73
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\ReportApi\Service;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager;

class ConfigProvider
{
    private $scopeConfig;

    private $moduleManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Manager $moduleManager
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->moduleManager = $moduleManager;
    }

    public function isGroupByDimensions(): bool
    {
        if ($this->moduleManager->isEnabled('Mirasvit_Reports')) {
            return (bool)$this->scopeConfig->getValue('mst_reports/general/group_by_dimensions');
        }

        return true;
    }
}
