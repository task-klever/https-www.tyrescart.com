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


namespace Mirasvit\Report\Service;


use Mirasvit\ReportApi\Api\RequestInterface;

class ResponseJsonService
{
    private $serializer;

    public function __construct(\Magento\Framework\Serialize\Serializer\Json $serializer)
    {
        $this->serializer = $serializer;
    }

    public function convertToJson(RequestInterface $request, bool $formatted = false): string
    {
        $result = [
            'columns' => [],
            'rows'    => [],
            'totals'  => []
        ];

        $response = $request->process();

        foreach ($response->getColumns() as $column) {
            $result['columns'][] = [
                'dataType' => $column->getType(),
                'name'     => $column->getName(),
                'label'    => (string)$column->getLabel()
            ];
        }

        foreach ($response->getItems() as $item) {
            $result['rows'][] = $formatted ? $item->getFormattedData() : $item->getData();
        }

        $result['totals'] = $formatted
            ? $response->getTotals()->getFormattedData()
            : $response->getTotals()->getData();

        return $this->serializer->serialize($result);
    }
}
