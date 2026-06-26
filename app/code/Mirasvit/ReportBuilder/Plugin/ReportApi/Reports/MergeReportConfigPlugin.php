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


namespace Mirasvit\ReportBuilder\Plugin\ReportApi\Reports;


use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;

class MergeReportConfigPlugin
{
    private $reportRepository;

    private $dateService;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        DateServiceInterface $dateService
    )
    {
        $this->reportRepository = $reportRepository;
        $this->dateService      = $dateService;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterInit(ReportInterface $subject, $result)
    {
        if (
            !is_numeric($subject->getIdentifier())
            && ($customized = $this->reportRepository->getByReportIdentifier($subject->getIdentifier()))
        ) {
            $subject->setIsSharingEnabled($customized->getIsSharingEnabled())
                ->setShareIdentifier($customized->getShareIdentifier())
                ->setSortOrders($customized->getSortOrders())
                ->setColumns($customized->getColumns())
                ->setFilters($customized->getFilters() ?: [])
                ->setTimeRange($customized->getTimeRange() ?: DateServiceInterface::THIS_MONTH)
                ->setDimensions($customized->getDimensions())
                ->setPrimaryDimensions($customized->getPrimaryDimensions())
                ->setPrimaryFilters($customized->getPrimaryFilters())
                ->setInternalFilters($customized->getInternalFilters())
                ->setIsCustomized(true);
        }

        $filters   = $subject->getFilters() ?: [];
        $timeRange = $subject->getTimeRange() ?: DateServiceInterface::THIS_MONTH;

        if ($timeRange !== DateServiceInterface::CUSTOM) { // update time range filter according to stored interval
            $primaryFilters  = $subject->getPrimaryFilters();
            $dateRangeFilter = null;

            foreach ($primaryFilters as $filter) {
                if (strpos($filter, '_at') !== false) {
                    $dateRangeFilter = $filter;
                    break;
                }
            }

            if ($dateRangeFilter) {
                $interval = $this->dateService->getInterval($timeRange);

                foreach ($filters as $idx => $filter) {
                    if ($filter['column'] == $dateRangeFilter) {
                        unset($filters[$idx]);
                    }
                }

                $filters[] = [
                    'column'        => $dateRangeFilter,
                    'conditionType' => 'gteq',
                    'value'         => $interval->getFrom()->toString('Y-MM-dd HH:mm:ss')
                ];

                $filters[] = [
                    'column'        => $dateRangeFilter,
                    'conditionType' => 'lteq',
                    'value'         => $interval->getTo()->toString('Y-MM-dd HH:mm:ss')
                ];
            }
        }

        $subject->setFilters($filters);

        return $result;
    }
}
