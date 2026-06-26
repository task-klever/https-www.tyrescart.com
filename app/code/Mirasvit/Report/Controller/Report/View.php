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


declare(strict_types=1);


namespace Mirasvit\Report\Controller\Report;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Model\Export\ConvertToCsv;
use Mirasvit\Report\Model\Export\ConvertToXml;
use Mirasvit\Report\Repository\ReportRepository;
use Mirasvit\Report\Service\ResponseJsonService;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;

class View extends Action
{
    private $resultLayoutFactory;

    private $repository;

    private $requestBuilder;

    private $fileFactory;

    private $convertToCsv;

    private $convertToXml;

    private $responseJsonService;

    private $registry;

    private $context;

    public function __construct(
        ResultLayoutFactory $resultLayoutFactory,
        ReportRepository $repository,
        FileFactory $fileFactory,
        ConvertToXml $convertToXml,
        ConvertToCsv $convertToCsv,
        RequestBuilderInterface $requestBuilder,
        ResponseJsonService $responseJsonService,
        Registry $registry,
        Context $context
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->repository          = $repository;
        $this->requestBuilder      = $requestBuilder;
        $this->fileFactory         = $fileFactory;
        $this->convertToXml        = $convertToXml;
        $this->convertToCsv        = $convertToCsv;
        $this->responseJsonService = $responseJsonService;
        $this->registry            = $registry;
        $this->context             = $context;

        parent::__construct($context);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $report  = null;
        $message = null;

        $identifier = $this->getRequest()->getParam('identifier');

        if ($identifier) {
            /** @var ReportInterface $item */
            foreach ($this->repository->getList() as $item) {
                $item->init();

                if ($item->getShareIdentifier() == $identifier) {
                    $report = $item;

                    break;
                }
            }

            if ($report && $report->getIsSharingEnabled()) {
                $format  = (string)$this->getRequest()->getParam('format');
                $request = $this->processRequest($report);

                switch ($this->getRequest()->getParam('format')) {
                    case 'csv':
                        $content = $this->convertToCsv->getCsvFile($request);

                        return $this->fileFactory->create('export.' . $format, $content, 'var');
                    case 'xml':
                        $content = $this->convertToXml->getXmlFile($request);

                        return $this->fileFactory->create('export.' . $format, $content, 'var');
                    case 'json':
                        return $this->getResponse()->representJson($this->responseJsonService->convertToJson($request));
                    case 'html':
                        $this->registry->register('current_report', $report);
                        $this->registry->register('current_request', $request);

                        return $this->resultLayoutFactory->create();
                    default:
                        $message = (string)__('Unsupported format "%1"', $format);
                }
            } else {
                $message = (string)__('This report does not exist or not available for sharing');
            }
        } else {
            $message = (string)__('Report identifier is required');
        }

        $this->registry->register('current_message', $message);

        return $this->resultLayoutFactory->create();
    }

    private function processRequest(ReportInterface $report)
    {
        $request = $this->requestBuilder->create();
        $request->setTable($report->getTable())
            ->setDimensions($report->getDimensions());

        foreach ($report->getDimensions() as $c) {
            $request->addColumn($c);
        }

        foreach ($report->getColumns() as $c) {
            $request->addColumn($c);
        }

        $internalFilters = $report->getInternalFilters() ? : [];
        $filters         = $report->getFilters() ? : [];

        foreach (array_merge($internalFilters, $filters) as $filter) {
            if ($filter['conditionType'] == 'like') {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            $request->addFilter($filter['column'], $filter['value'], $filter['conditionType']);
        }

        $request->setIdentifier($report->getIdentifier());

        $sortOrders = $report->getSortOrders() ? : [];

        foreach ($sortOrders as $sortOrder) {
            $request->addSortOrder($sortOrder['column'], $sortOrder['direction']);
        }

        return $request;
    }
}
