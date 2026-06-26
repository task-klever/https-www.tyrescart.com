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



namespace Mirasvit\ReportBuilder\Plugin\ReportApi\Repository\ReportRepository;

use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportBuilder\Service\BuilderService;

class AddReportsPlugin
{
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var BuilderService
     */
    private $builderService;

    /**
     * AddReportsPlugin constructor.
     * @param ReportRepositoryInterface $reportRepository
     * @param BuilderService $builderService
     */
    public function __construct(
        ReportRepositoryInterface $reportRepository,
        BuilderService $builderService
    ) {
        $this->reportRepository = $reportRepository;
        $this->builderService = $builderService;
    }

    /**
     * @param mixed $subject
     * @param mixed $result
     * @return array
     */
    public function afterGetList($subject, $result)
    {
        //avoid duplicate predefined reports
        $customReports = $this->reportRepository
            ->getCollection()
            ->addFieldToFilter(ReportInterface::REPORT_IDENTIFIER, ['null' => true]);

        foreach ($customReports as $report) {
            $result[] = $this->builderService->getReportInstance($report);
        }

        return $result;
    }

    public function afterGet($subject, $result, $identifier)
    {
        if (!$result) {
            return $result;
        }

        if (!$identifier) {
            return $result;
        }

        /** @var ReportInterface $customized */
        if ($customized = $this->reportRepository->getByReportIdentifier($identifier)) {
            $result->setIsSharingEnabled($customized->getIsSharingEnabled())
                ->setShareIdentifier($customized->getShareIdentifier())
                ->setSortOrders($customized->getSortOrders())
                ->setColumns($customized->getColumns())
                ->setFilters($customized->getFilters() ?: [])
                ->setDimensions($customized->getDimensions())
                ->setPrimaryDimensions($customized->getPrimaryDimensions())
                ->setPrimaryFilters($customized->getPrimaryFilters())
                ->setInternalFilters($customized->getInternalFilters());
        }

        return $result;
    }
}
