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



namespace Mirasvit\Report\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filter\FilterManager;
use Mirasvit\Report\Model\Export\ConvertToCsv;
use Mirasvit\Report\Model\Export\ConvertToXml;
use Mirasvit\Report\Repository\ReportRepository;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;

class Export extends AbstractApi
{
    const DEFAULT_FILE_NAME = 'export';

    private $fileFactory;

    private $convertToXml;

    private $convertToCsv;

    private $requestBuilder;

    private $reportRepository;

    private $filter;

    public function __construct(
        FileFactory $fileFactory,
        ConvertToXml $convertToXml,
        ConvertToCsv $convertToCsv,
        RequestBuilderInterface $requestBuilder,
        ReportRepository $repository,
        FilterManager $filter,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Context $context
    ) {
        $this->fileFactory      = $fileFactory;
        $this->convertToXml     = $convertToXml;
        $this->convertToCsv     = $convertToCsv;
        $this->requestBuilder   = $requestBuilder;
        $this->reportRepository = $repository;
        $this->filter           = $filter;

        parent::__construct($context, $serializer);
    }

    public function execute()
    {
        $type = $this->getRequest()->getParam('type');

        $response = $this->processRequest();

        if ($type === 'xml') {
            $content = $this->convertToXml->getXmlFile($response);
        } else {
            $content = $this->convertToCsv->getCsvFile($response);
        }

        $filename = self::DEFAULT_FILE_NAME;

        $report = $this->reportRepository->get($this->getRequest()->getParam('identifier'));

        if ($report) {
            $filename = $this->filter->translitUrl($report->getName()) . '_' . date('Y-m-d_H-i-s');
        }

        return $this->fileFactory->create($filename . '.' . $type, $content, 'var');
    }

    /**
     * @return \Mirasvit\ReportApi\Api\RequestInterface
     */
    private function processRequest()
    {
        $r = $this->getRequest();

        $request = $this->requestBuilder->create();
        $request->setTable($r->getParam('table'))
            ->setDimensions($r->getParam('dimensions'));

        foreach ($r->getParam('dimensions', []) as $c) {
            $request->addColumn($c);
        }

        foreach ($r->getParam('columns', []) as $c) {
            $request->addColumn($c);
        }

        foreach ($r->getParam('filters', []) as $filter) {
            if ($filter['conditionType'] == 'like') {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            $request->addFilter($filter['column'], $filter['value'], $filter['conditionType']);
        }

        $request->setIdentifier($r->getParam('identifier'));

        foreach ($r->getParam('sortOrders', []) as $sortOrder) {
            $request->addSortOrder($sortOrder['column'], $sortOrder['direction']);
        }

        return $request;
    }
}
