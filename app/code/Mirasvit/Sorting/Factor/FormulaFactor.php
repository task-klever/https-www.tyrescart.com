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
 * @package   mirasvit/module-sorting
 * @version   1.3.15
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Sorting\Factor;


use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use PhpOffice\PhpSpreadsheet;


class FormulaFactor implements FactorInterface
{
    const FORMULA = 'formula';

    const EXCEPTION_PREFIX = 'Invalid formula: ';

    private $context;

    private $indexer;

    public function __construct(
        Context $context,
        FactorIndexer $indexer
    ) {
        $this->context    = $context;
        $this->indexer    = $indexer;
    }

    public function getName(): string
    {
        return 'Formula';
    }

    public function getDescription(): string
    {
        return 'Rank products based on the formula';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_formula';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $formula   = $rankingFactor->getConfigData(self::FORMULA);
        $variables = $this->parseVariables($formula);

        $this->validateFormula($formula, $variables);

        $result = $this->getCalculatedResult($formula, $variables, $this->retrieveData($variables));

        $this->indexer->process($rankingFactor, $productIds, function () use ($result) {
            foreach ($result as $row) {
                $this->indexer->add((int)$row['product_id'], $row['score'], $row['value'], $row['store_id']);
            }
        });
    }

    public function parseVariables(string $formula): array
    {
        $variables = [];

        preg_match_all('/\{([^\}]{8,})\}/i', $formula, $match);

        if (count($match) !== 2 || count($match[0]) !== count($match[1])) {
            return [];
        }

        foreach (array_unique($match[1]) as $variable) {
            if (strpos($variable, 'factor_') === 0) {

                $this->validateFactorVariable($variable);

                $varData = [
                    'table'  => $this->indexer->getResource()->getTableName(IndexInterface::TABLE_NAME),
                    'column' => $variable . '_score',
                    'type'   => 'factor'
                ];

                $variables[$variable] = $varData;

                continue;
            }

            if (strpos($variable, 'product_') !== 0) {
                throw new \Exception(
                    self::EXCEPTION_PREFIX . 'Incorrect variable "' . $variable
                    . '". Allowed variables are "product_[attribute_code]" or "factor_[factor_id]"'
                );
            }

            $attrCode = substr($variable, 8);

            $attribute = $this->context->eavConfig->getAttribute('catalog_product', $attrCode);

            if (!$attribute) {
                throw new \Exception(self::EXCEPTION_PREFIX . 'Attribute with the code "' . $attrCode . '" does not exists');
            }

            $backendType   = $attribute->getBackendType();
            $frontendInput = $attribute->getFrontendInput();

            if (in_array($frontendInput, ['gallery', 'media_image'])) {
                throw new \Exception(self::EXCEPTION_PREFIX . 'Attributes with types "media_image" and "gallery" are not allowed');
            }

            if ($backendType === 'static') {
                $varData = [
                    'table'  => $attribute->getBackend()->getTable(),
                    'column' => $attrCode,
                    'type'   => 'static'
                ];
            } else {
                $varData = [
                    'table'        => $attribute->getBackend()->getTable(),
                    'column'       => 'value',
                    'attribute_id' => $attribute->getId(),
                    'type'         => $frontendInput
                ];
            }

            $variables[$variable] = $varData;
        }

        return $variables;
    }

    public function validateFormula(string $formula, array $variables): void
    {
        if (strlen($formula) > 0 && strpos($formula, '=') !== 0) {
            throw new \Exception(self::EXCEPTION_PREFIX . "Formula MUST begin with '='");
        }

        $data = [];

        foreach (array_merge(['entity_id', 'store_id'], array_keys($variables)) as $variable) {
            $data[$variable] = rand(1, 10);
        }

        try {
            $this->getCalculatedResult($formula, $variables, [$data]);
        } catch (\Exception $e) {
            throw new \Exception(self::EXCEPTION_PREFIX . $e->getMessage());
        }
    }

    private function validateFactorVariable(string $variable): void
    {
        $id         = substr($variable, 7);
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select()
            ->from(['e' => $resource->getTableName(RankingFactorInterface::TABLE_NAME)], ['factor_id', 'type'])
            ->where('e.factor_id = ' . $id)
            ->limit(1);

        $result = $connection->query($select)->fetchAll();

        if (!count($result)) {
            throw new \Exception(self::EXCEPTION_PREFIX . 'Factor with ID:' . $id . ' does not exists');
        }

        if ($result[0]['type'] === self::FORMULA) {
            throw new \Exception(self::EXCEPTION_PREFIX . 'Factors with the type "formula" are not allowed');
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function retrieveData(array $variables): array
    {
        $staticColumns  = ['entity_id' => 'e.entity_id'];
        $factorColumns  = [];
        $addStoreColumn = false;
        $groupColumns   = ['e.entity_id'];

        foreach ($variables as $variable => $varData) {
            switch ($varData['type']) {
                case 'static':
                    $staticColumns[$variable] = $varData['column'];
                    break;
                case 'factor':
                    $factorColumns[$variable] = $varData['column'];
                    break;
                default:
                    break;
            }
        }

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();
        $select     = $connection->select()->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            $staticColumns
        );

        foreach ($variables as $variable => $varData) {
            if (in_array($varData['type'], ['factor', 'static'])) {
                continue;
            }

            $addStoreColumn = true;
            if (!in_array('store_id', $groupColumns)) {
                $groupColumns[] = 'store_id';
            }

            $tableAlias = 'eav_' . $variable;
            if (in_array($varData['type'], ['select', 'multiselect'])) {
                $optionTableAlias = 'eav_option_' . $variable;
                $optionCondition  = $varData['type'] === 'select'
                    ? "{$optionTableAlias}.option_id = {$tableAlias}.value"
                    : "FIND_IN_SET({$optionTableAlias}.option_id, {$tableAlias}.value)";

                $select->joinLeft(
                    [$tableAlias => $varData['table']],
                    implode(' AND ', [
                        "{$tableAlias}.attribute_id = {$varData['attribute_id']}",
                        $this->getAttributeProductConrition($tableAlias)
                    ]),
                    [null]
                )->joinLeft(
                    [$optionTableAlias => $resource->getTableName('eav_attribute_option_value')],
                    implode(' AND ', [
                        $optionCondition,
                        "{$optionTableAlias}.store_id = {$tableAlias}.store_id"
                            ]),
                    [$variable => "GROUP_CONCAT(DISTINCT {$optionTableAlias}.value)"]
                );
            } else {
                $select->joinLeft(
                    [$tableAlias => $varData['table']],
                    implode(' AND ', [
                        "{$tableAlias}.attribute_id = {$varData['attribute_id']}",
                        $this->getAttributeProductConrition($tableAlias)
                    ]),
                    [$variable => $varData['column']]
                );
            }
        }

        if (count($factorColumns)) {
            $condition = 'factor.product_id = e.entity_id';
            if ((count($factorColumns) + count($staticColumns)) <= count($variables)) {
                $condition .= ' AND factor.store_id = ' . $tableAlias . '.store_id';
            }

            $factorColumns['store_id'] = 'factor.store_id';
            if (!in_array('store_id', $groupColumns)) {
                $groupColumns[] = 'store_id';
            }

            $addStoreColumn = false;
            $select->joinLeft(
                ['factor' => $resource->getTableName(IndexInterface::TABLE_NAME)],
                'factor.product_id = e.entity_id',
                $factorColumns
            );
        }

        if ($addStoreColumn) {
            $select->columns(['store_id' => "IFNULL({$tableAlias}.store_id, 0)"]);
        }

        $select->group($groupColumns)->order('e.entity_id');

        return $connection->query($select)->fetchAll();
    }

    private function prepareFormula(string $formula, array $variables = []): string
    {
        foreach ($variables as $variable => $data) {
            $formula = str_replace('{' . $variable . '}', $variable, $formula);
        }

        return $formula;
    }

    /**
     * Calculates score using data placed directly into the formula
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getCalculatedResult(string $formula, array $variables, array $data): array
    {
        if (!class_exists(PhpSpreadsheet\Spreadsheet::class)) {
            throw new \Exception("Required package \"phpoffice/phpspreadsheet\" missed");
        }

        $spreadsheet = new PhpSpreadsheet\Spreadsheet();
        $worksheet   = $spreadsheet->getActiveSheet();

        $preparedFormula = $this->prepareFormula($formula, $variables);

        $toWorksheet = [];

        foreach ($data as $row) {
            $pFormula = $preparedFormula;

            $item = [
                $row['entity_id'],
                isset($row['store_id']) && $row['store_id'] ? $row['store_id'] : 0
            ];

            foreach ($variables as $variable => $varData) {
                $value = isset($row[$variable]) ? $row[$variable] : null;
                $value = is_numeric($value)
                    ? (string)$value
                    : '"' . str_replace('"', '\'', (string)$value) . '"';

                $pFormula = str_replace(
                    $variable,
                    $value,
                    $pFormula
                );
            }
            $item[] = $pFormula;

            $toWorksheet[] = $item;
        }

        $worksheet->fromArray($toWorksheet);

        $result = [];

        foreach ($worksheet->toArray() as $idx => $row) {
            $result[] = [
                'product_id' => $row[0],
                'store_id'   => $row[1] ?: 0,
                'score'      => is_numeric($row[count($row)-1]) ? $row[count($row)-1] : null,
                'value'      => str_replace('""', 'NULL', $worksheet->getCell('C' . ($idx+1))->getValue())
            ];
        }

        return $result;
    }

    private function getAttributeProductConrition(string $tableAlias): string
    {
        return CompatibilityService::isEnterprise()
            ? "{$tableAlias}.row_id = e.row_id"
            : "{$tableAlias}.entity_id = e.entity_id";
    }
}
