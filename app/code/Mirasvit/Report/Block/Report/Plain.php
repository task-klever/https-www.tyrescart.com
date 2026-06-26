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


namespace Mirasvit\Report\Block\Report;


use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Mirasvit\ReportApi\Api\Processor\ResponseColumnInterface;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;

class Plain extends Template
{
    protected $_template = "Mirasvit_Report::report/plain.phtml";

    private $registry;

    private $header = [];

    private $footer = [];

    private $rows = [];

    public function __construct(
        Registry $registry,
        Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    public function getReport()
    {
        return $this->registry->registry('current_report');
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function buildReportContent(): bool
    {
        $request = $this->registry->registry('current_request');

        if (!$request) {
            return false;
        }

        return $this->buildReport($request);
    }

    private function buildReport($request)
    {
        $response = $request->process();

        $rows = [];
        foreach ($response->getColumns() as $column) {
            $this->header[] = $column->getLabel();
            $rows['header'][] = $column->getLabel();
        }

        foreach ($response->getItems() as $item) {
            $this->addRow($this->rows, $item, $response->getColumns());
        }

        foreach ($response->getTotals()->getFormattedData() as $key => $value) {
            $this->footer[] = $value;
            $rows['footer'][] = $value;
        }

        return true;
    }

    private function addRow(&$rows, ResponseItemInterface $item, array $columns)
    {
        $formattedData = $item->getFormattedData();

        $data = [];
        /** @var ResponseColumnInterface $column */
        foreach ($columns as $column) {
            $name = $column->getName();

            if (isset($formattedData[$name])) {
                $data[] = $formattedData[$name];
            } else {
                $data[] = '';
            }
        }

        $rows[] = $data;

        foreach ($item->getItems() as $subItem) {
            $this->addRow($rows, $subItem, $columns);
        }
    }

    protected function _toHtml()
    {
        if ($this->buildReportContent()) {
            return parent::_toHtml();
        } else {
            return (string)$this->registry->registry('current_message')
                ?: 'Something went wrong while building the report';
        }
    }
}
