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



namespace Mirasvit\Reports\Service\Pills;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Api\Service\SelectPillInterface;
use Mirasvit\ReportApi\Config\Aggregator\None;
use Mirasvit\ReportApi\Handler\Select;

/**
 * Class ChildItemPill
 * Add parent item's sales values to child items.
 * Because child items (simple products) do not have such values.
 * These values are stored on a parent item level (configurable products).
 */
class ChildItemPill implements SelectPillInterface
{
    private $attributeRepository;

    private $resource;

    public function __construct(
        ResourceConnection $resource,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resource            = $resource;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @inheritdoc
     */
    public function isApplicable(RequestInterface $request, ColumnInterface $column, TableInterface $table)
    {
        if ($column->getTable()->getName() === 'sales_order_item') {
            return true;
        }

        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * Temporary fix to retrieve correct sales values from the parent
     * item instead of a child (it does not have sales values).
     * Fix applied only if the dimension used for report is the attribute used for configurable products.
     * {@inheritdoc}
     */
    public function take(
        Select $select,
        ColumnInterface $column,
        TableInterface $baseTable,
        RequestInterface $request
    ) {
        $applicableFields = [];

        foreach ($column->getFields() as $field) {
            if ($this->isAttributeApplicable($field->getName())) {
                $applicableFields[] = $field->getName();
            }
        }

        if (!count($applicableFields)) {
            return;
        }

        $alias = 'sales_order_item_parent' . rand(0, 1000);

        #remove old column
        $columns = $select->getPart('columns');
        foreach ($columns as $idx => $item) {
            if ($item[2] === $column->getName()) {
                unset($columns[$idx]);
            }
        }
        $select->setPart('columns', $columns);

        $origAggregator = $column->getAggregator();
        $noneAggregator = ObjectManager::getInstance()->create(None::class);

        // unset original aggregator
        $column->setAggregator($noneAggregator);

        $expression = $column->toDbExpr();

        // apply pill to each field separately
        foreach ($column->getFields() as $field) {
            $exprFieldName = 'sales_order_item.'.$field->getName();

            if (strpos($expression, $exprFieldName) === false) { // ignore fields from other tables
                continue;
            }

            if (!in_array($field->getName(), $applicableFields)) {
                continue;
            }

            if (in_array($field->getName(), ['price', 'base_price', 'original_price', 'row_total', 'base_row_total'])) {
                // If bundle product has static price (Dynamic Price => No)
                // then child products don't have prices and totals in the sales_order_item table.
                // But has price in the product_options column
                // In this case we exctract price from product_options (child of bundle product)
                // And only then we use parent value for the child (child of configurable product)

                // JSON functions not supported by MySQL MySQL < 5.7.8 or MariaDB < 10.2.3
                $fieldExpr = 'IF(' . $exprFieldName . ' > 0, ' . $exprFieldName . ', '
                    . 'IFNULL(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(sales_order_item.product_options, "$.bundle_selection_attributes")), "$.price"), ' . $alias . '.' . $field->getName() . '))';
            } else {
                $fieldExpr = 'IF(' . $exprFieldName . ' = 0, ' . $alias . '.' . $field->getName() . ', ' . $exprFieldName . ')';
            }

            $expression = str_replace($exprFieldName, $fieldExpr, $expression);
        }

        // apply original aggregator to modified expression
        $expression = str_replace('%1', $expression, $origAggregator->getExpression());

        $select->columns([
            $column->getName() => new \Zend_Db_Expr($expression),
        ]);

        $select->joinLeft(
            [$alias => $this->resource->getTableName($column->getTable()->getName())],
            $column->getTable()->getName() . '.parent_item_id = ' . $alias . '.item_id',
            ''
        );

        // reset original aggregator
        $column->setAggregator($origAggregator);
    }

    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    public function isAttributeApplicable($attributeCode)
    {
        if (preg_match('/qty|amount|total|tax|price|rule/', $attributeCode)) {
            return true;
        }

        return false;
    }
}
