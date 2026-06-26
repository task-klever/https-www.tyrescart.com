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



namespace Mirasvit\ReportApi\Processor;


use Mirasvit\Core\Model\Date;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Api\ResponseInterface;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Service\ConfigProvider;

class ResponseBuilder
{
    private $responseFactory;

    private $responseItemFactory;

    private $responseColumnFactory;

    private $schema;

    private $configProvider;

    public function __construct(
        ResponseFactory $responseFactory,
        ResponseItemFactory $responseItemFactory,
        ResponseColumnFactory $responseColumnFactory,
        Schema $schema,
        ConfigProvider $configProvider
    ) {
        $this->responseFactory       = $responseFactory;
        $this->responseItemFactory   = $responseItemFactory;
        $this->responseColumnFactory = $responseColumnFactory;
        $this->schema                = $schema;
        $this->configProvider        = $configProvider;
    }

    /**
     * @param RequestInterface                         $request
     * @param \Mirasvit\ReportApi\Handler\Collection[] $collections
     * @return ResponseInterface
     */
    public function create(RequestInterface $request, array $collections)
    {
        $groups = [];
        foreach (array_keys($collections) as $group) {
            $groups[$group] = [];
        }
        foreach ($collections as $group => $collection) {
            foreach ($collection as $data) {
                $pk = '';

                foreach ($request->getDimensions() as $dimension) {
                    $pk .= $this->getPk($dimension, $data, $groups[$group]);
                }
                $groups[$group][$pk] = $data;
            }
        }

        if (count($groups) == 2) { // dashboard with comparision
            $groups = $this->fillDataForComparison($request, $groups);
        }

        //        foreach ($groups['A'] as $pk => $data) {
        //            foreach ($groups as $group => $items) {
        //                if ($group != 'A') {
        //                    foreach ($items as $sPk => $itm) {
        //                        if ($pk == $sPk) {
        //                            foreach ($itm as $k => $v) {
        //                                $data["$group|$k"] = $v;
        //                            }
        //                        }
        //                    }
        //                }
        //            }
        //
        //            foreach ($request->getDimensions() as $dimension) {
        //                $value          = $data[$dimension];
        //                $result[$value] = $data;
        //            }
        //
        //            $result[] = $this->responseItemFactory->create(['data' => [
        //                ResponseItem::DATA           => $data,
        //                ResponseItem::FORMATTED_DATA => $this->getFormattedData($data),
        //            ]]);
        //        }

        foreach ($groups['A'] as $key => $data) {
            $itemData = [
                ResponseItem::DATA           => $data,
                ResponseItem::FORMATTED_DATA => $this->getFormattedData($data, true),
            ];

            foreach ($groups as $group => $items) {
                if ($group != 'A') {
                    foreach ($items as $sPk => $itm) {
                        if ($key == $sPk) {
                            foreach ($itm as $k => $v) {
                                $itemData[ResponseItem::DATA]["$group|$k"] = $v;
                            }
                            $fItm = $this->getFormattedData($itm);
                            foreach ($fItm as $k => $v) {
                                $itemData[ResponseItem::FORMATTED_DATA]["$group|$k"] = $v;
                            }
                        }
                    }
                }
            }

            $groups['A'][$key] = $this->responseItemFactory->create(['data' => $itemData]);
        }

        // group report data by dimensions (default = yes), can be changed only in Mirasvit_Reports
        if ($this->configProvider->isGroupByDimensions()) {
            $result = $this->groupByDimensions($request->getDimensions(), $groups['A']);
        } else {
            $result = $groups['A'];
        }

        $columns = [];
        foreach ($request->getColumns() as $name) {
            $column    = $this->schema->getColumn($name);
            $columns[] = $this->responseColumnFactory->create(['data' => [
                ResponseColumn::NAME  => $name,
                ResponseColumn::LABEL => $column->getLabel(),
                ResponseColumn::TYPE  => $column->getType()->getJsType(),
            ]]);
        }

        $totalsData = $collections['A']->getTotals();
        foreach ($collections as $group => $collection) {
            if ($group == 'A') {
                continue;
            }

            foreach ($collection->getTotals() as $k => $v) {
                $totalsData["$group|$k"] = $v;
            }
        }

        $data = [
            Response::SIZE    => $collections['A']->getSize(),
            Response::TOTALS  => $this->responseItemFactory->create(['data' => [
                ResponseItem::DATA           => $totalsData,
                ResponseItem::FORMATTED_DATA => $this->getFormattedData($totalsData, true),
            ]]),
            Response::ITEMS   => $result,
            Response::COLUMNS => $columns,
            Response::REQUEST => $request,
        ];

        // in some cases when result set contains only 1 row the totals may be empty
        // so we simply put the result items in totals
        if (!$totalsData && $data[Response::SIZE] == 1) {
            $data[Response::TOTALS] = reset($result);
        }

        $response = $this->responseFactory->create(
            ['data' => $data]
        );

        return $response;
    }

    private function fillDataForComparison(RequestInterface $request, array $groups): array
    {
        $dimensionField  = $request->getDimensions()[0];
        $dimensionColumn = $this->schema->getColumn($dimensionField);

        if ($dimensionField !== $request->getColumns()[0]) {
            return $groups; // charts have dimension and first column equal, otherwise it's a sparkline
        }

        if (count($dimensionColumn->getFields()) !== 1) {
            return $groups; // ignore complex dimension fields just in case
        }

        $fieldName = $dimensionColumn->getFields()[0]->getIdentifier();

        $applicapableFilters = [];

        // collecting filters by dimension column
        foreach ($request->getFilters() as $filter) {
            if ($filter->getColumn() == $fieldName) {
                if (!isset($applicapableFilters[$filter->getGroup()])) {
                    $applicapableFilters[$filter->getGroup()] = [];
                }

                $applicapableFilters[$filter->getGroup()][] = $filter;
            }
        }

        // generating dates in date range with aggregation step
        $aggregatorType  = $dimensionColumn->getAggregator()->getType();
        $dateShiftMethod = 'add' . ucfirst($aggregatorType);

        $now = new Date(time());

        foreach ($applicapableFilters as $group => $filters) {
            $from = null;
            $to   = null;

            foreach ($filters as $filter) {
                if ($filter->getConditionType() == 'gteq') {
                    $from = new Date($filter->getValue());
                } else {
                    $to = new Date($filter->getValue());
                }
            }

            $existData = [];

            foreach (array_values($groups[$group]) as $responseItemData) {
                $existData[$responseItemData[$dimensionField]] = $responseItemData;
            }

            $groups[$group] = [];

            $idx = 0;

            // if date range is "something" to date we crop data to current date
            if ($to->getTimestamp() > $now->getTimestamp()) {
                $to->subTimestamp($to->getTimestamp() - $now->getTimestamp());
            }

            while ($from->getTimestamp() <= $to->getTimestamp()) {
                $date = $from->toString('Y-MM-dd HH:mm:ss');

                if (isset($existData[$date])) {
                    $groups[$group][$idx] = $existData[$date];
                } else {
                    $groups[$group][$idx]                  = array_fill_keys($request->getColumns(), null);
                    $groups[$group][$idx][$dimensionField] = $date;
                }

                $idx++;
                $from->$dateShiftMethod(1);
            }
        }


        return $groups;
    }

    /**
     * @param string[]                $dimensions
     * @param ResponseItemInterface[] $data
     * @param int                     $depth
     * @return array
     */
    private function groupByDimensions($dimensions, $data, $depth = 0)
    {
        if (count($dimensions) == 0) {
            return $data;
        }

        $dimension = $dimensions[$depth];

        $result = [];
        foreach ($data as $item) {
            $value = $item->getData($dimension);

            if (!isset($result[$value])) {
                $result[$value] = $this->responseItemFactory->create(['data' => [
                    ResponseItem::DATA           => [
                        $dimension => $item->getData($dimension),
                    ],
                    ResponseItem::FORMATTED_DATA => [
                        $dimension => $item->getFormattedData($dimension),
                    ],
                ]]);
            }

            if ($depth > 0) {
                $item->unsetData($dimensions[$depth - 1]);
            }

            $result[$value]->addItem($item);
        }

        if ($depth + 1 < count($dimensions)) {
            foreach ($result as $d => $data) {
                $result[$d]->setItems(
                    $this->groupByDimensions($dimensions, $data->getItems(), $depth + 1)
                );
            }
        } else {
            foreach ($result as $d => $data) {
                $result[$d] = $result[$d]->getItems()[0];
            }
        }

        return array_values($result);
    }

    /**
     * @param mixed $dimension
     * @param array $data
     * @param array $items
     * @return string
     * @throws \Exception
     */
    private function getPk($dimension, $data, $items)
    {
        $dimensionColumn = $this->schema->getColumn($dimension);

        if (isset($data[$dimension])) {
            $pk = $dimensionColumn->getType()->getPk($data[$dimension], $dimensionColumn->getAggregator());
        } else {
            $pk = 0;
        }

        $idx = 0;
        while (isset($items["{$pk}_{$idx}"])) {
            $idx++;
        }

        return "{$pk}_{$idx}";
    }

    /**
     * @param array $data
     * @param bool $isTotals
     * @return array
     * @throws \Exception
     */
    private function getFormattedData($data, $isTotals = false)
    {
        $formattedData = [];
        foreach ($data as $name => $value) {
            $column = $this->schema->getColumn($name);

            $formattedData[$name] = $column->getType()->getFormattedValue($value, $column->getAggregator());

            if ($isTotals && $value === null) {
                $formattedData[$name] = null;
            }
        }

        return $formattedData;
    }
}
