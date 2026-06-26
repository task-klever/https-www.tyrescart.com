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
 * @package   mirasvit/module-reports
 * @version   1.6.0
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Reports\Plugin\Framework\Acl;

use Magento\Framework\Acl\AclResource\ProviderInterface as AclResourceProvider;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;

class AddReportsToAclPlugin
{
    private $reportRepository;

    public function __construct(ReportRepositoryInterface $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterGetAclResources(AclResourceProvider $subject, array $result): array
    {
        if (empty($result)) {
            return $result;
        }

        foreach ($result as $k1 => $v1) {
            if ($v1['id'] !== 'Magento_Backend::admin') {
                continue;
            }

            foreach ($v1['children'] as $k2 => $v2) {
                if ($v2['id'] !== 'Magento_Reports::report') {
                    continue;
                }

                foreach ($v2['children'] as $k3 => $v3) {
                    if ($v3['id'] !== 'Mirasvit_Reports::advanced_reports') {
                        continue;
                    }

                    foreach ($v3['children'] as $k4 => $v4) {
                        if ($v4['id'] !== 'Mirasvit_Reports::reports_view') {
                            continue;
                        } else {
                            $result[$k1]['children'][$k2]['children'][$k3]['children'][$k4]['children'] = $this->buildReportsAclConfig();
                            break 4;
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function buildReportsAclConfig()
    {
        $config = [];

        foreach ($this->reportRepository->getList() as $report) {
            if (!$report->getName()) {
                continue;
            }

            $tree[] = [
                'id'        => 'Mirasvit_Reports::reports_view_' . $report->getIdentifier(),
                'title'     => (string)$report->getName(),
                'sortOrder' => 10,
                'children'  => []
            ];
        }

        return $tree;
    }
}
