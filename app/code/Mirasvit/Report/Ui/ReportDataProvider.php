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
 * @package   mirasvit/module-report
 * @version   1.4.38
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Ui;

use Magento\Backend\Block\Template;
use Magento\Framework\Profiler;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\CspService;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Service\StateService;
use Mirasvit\ReportApi\Api\Service\ColumnServiceInterface;

class ReportDataProvider extends Template
{
    private $reportRepository;

    private $columnService;

    private $stateService;

    private $registry;

    private $urlBuilder;

    private $serializer;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        ColumnServiceInterface $columnService,
        StateService $stateService,
        Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Template\Context $context
    ) {
        $this->reportRepository = $reportRepository;
        $this->columnService    = $columnService;
        $this->stateService     = $stateService;
        $this->registry         = $registry;
        $this->serializer       = $serializer;
        $this->urlBuilder       = $context->getUrlBuilder();

        parent::__construct($context);
    }

    /**
     * @return array|null
     */
    public function getConfigData()
    {
        Profiler::start(__METHOD__);

        $currentReport = $this->getReport();

        if (!$currentReport) {
            return null;
        }

        $result = [
            'report'     => $currentReport->getIdentifier(),
            'reports'    => [],
            'requestUrl' => $this->getApiRequestUrl(),
            'stateUrl'   => $this->getApiStateUrl(),
            'exportUrl'  => $this->getApiExportUrl(),
        ];

        foreach ($this->reportRepository->getList() as $report) {
            if ($report->getIdentifier() !== $currentReport->getIdentifier()) {
                continue;
            }

            $report->init();

            $applicableColumns = $this->columnService->getApplicableColumns($report->getDimensions());

            $state = [
                'identifier'   => $report->getIdentifier(),
                'table'        => $report->getTable(),
                'dimensions'   => $report->getDimensions(),
                'columns'      => $report->getColumns(),
                'filters'      => $report->getFilters(),
                'sortOrders'   => $report->getSortOrders(),
                'currentPage'  => 1,
                'pageSize'     => $report->getPageSize() ?: 20,
                'chartType'    => $report->getChartConfig()->getType(),
                'chartColumns' => $report->getChartConfig()->getDefaultColumns(),
                'timeRange'    => (string)$report->getTimeRange()
            ];

//            $state = $this->stateService->mergeState($report->getIdentifier(), $state);

            $schema = [
                'primaryFilters'       => $report->getPrimaryFilters(),
                'primaryDimensions'    => $report->getPrimaryDimensions(),
                'applicableDimensions' => $this->columnService->getApplicableDimensions($report->getPrimaryDimensions()),
                'applicableColumns'    => $applicableColumns,
                'internalColumns'      => $report->getInternalColumns(),
                'internalFilters'      => $report->getInternalFilters(),
            ];

            if (method_exists($report, 'getApplicableColumns')) {
                $schema['applicableColumns'] = $report->getApplicableColumns();
            }

            if (method_exists($report, 'getApplicableDimensions')) {
                $schema['applicableDimensions'] = $report->getApplicableDimensions();
            }

            $result['reports'][$report->getIdentifier()] = [
                'identifier'      => $report->getIdentifier(),
                'name'            => $report->getName(),
                'state'           => $state,
                'schema'          => $schema,
                'isShareEnabled'  => $report->getIsSharingEnabled(),
                'shareIdentifier' => $report->getShareIdentifier(),
                'isCustomized'    => $report->getIsCustomized()
            ];
        }

        Profiler::stop(__METHOD__);

        return $result;
    }

    /**
     * @return string
     */
    public function getApiRequestUrl()
    {
        return $this->urlBuilder->getUrl('report/api/request');
    }

    /**
     * @return string
     */
    public function getApiStateUrl()
    {
        return $this->urlBuilder->getUrl('report/api/state');
    }

    /**
     * @return string
     */
    public function getApiExportUrl()
    {
        return $this->urlBuilder->getUrl('report/api/export');
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        try {
            $json = $this->serializer->serialize($this->getConfigData());
        } catch (\Exception $e) {
            return "<div class='message message-error'>" . $e->getMessage() . "</div>";
        }

        $nonce  = CspService::getNonce();
        $script = $nonce ? '<script nonce="' . $this->escapeHtml($nonce) . '">' : '<script>';

        return $script . "var reportDataProvider = $json</script>";
    }

    /**
     * @return ReportInterface
     */
    private function getReport()
    {
        return $this->registry->registry('current_report');
    }
}
