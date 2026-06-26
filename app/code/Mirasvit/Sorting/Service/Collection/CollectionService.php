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

namespace Mirasvit\Sorting\Service\Collection;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Sorting\Api\Data\IndexInterface;

class CollectionService
{
    private $resource;

    private $attributeRepository;

    private $tableMaintainer;

    private $storeManager;

    private $registry;

    public function __construct(
        ResourceConnection $resource,
        ProductAttributeRepositoryInterface $attributeRepository,
        TableMaintainer $tableMaintainer,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->resource            = $resource;
        $this->attributeRepository = $attributeRepository;
        $this->tableMaintainer     = $tableMaintainer;
        $this->storeManager        = $storeManager;
        $this->registry            = $registry;
    }

    public function joinSortingIndex(Select $select): void
    {
        $tableName = $this->resource->getTableName(IndexInterface::TABLE_NAME);
        $storeId   = (int)$this->storeManager->getStore()->getId();

        $this->joinTable(
            $select,
            IndexInterface::TABLE_NAME.'_'. $storeId,
            $tableName,
            [
                IndexInterface::TABLE_NAME.'_'. $storeId . '.product_id = e.entity_id',
                IndexInterface::TABLE_NAME.'_'. $storeId . ".store_id = $storeId"
            ]
        );

        $this->joinTable(
            $select,
            IndexInterface::TABLE_NAME.'_0',
            $tableName,
            [
                IndexInterface::TABLE_NAME.'_0' . '.product_id = e.entity_id',
                IndexInterface::TABLE_NAME.'_0' . ".store_id = 0"
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function joinAttribute(Select $select, string $attributeCode): ?string
    {
        $storeId    = (int)$this->storeManager->getStore()->getId();
        $websiteId  = (int)$this->storeManager->getWebsite()->getId();
        $tableAlias = 'sorting_' . $attributeCode;

        if ($attributeCode == 'position') {
            $categoryIds = $this->retrieveCategoryIds($select);

            if (!count($categoryIds)) {
                return null;
            }

            $this->joinTable(
                $select,
                $tableAlias,
                $this->tableMaintainer->getMainTable($storeId),
                [
                    "{$tableAlias}.product_id = e.entity_id",
                    "{$tableAlias}.store_id = {$storeId}",
                    "{$tableAlias}.category_id IN (" . implode(',', $categoryIds) . ")",
                ]
            );

            return $tableAlias . '.position';
        } elseif ($attributeCode == 'price') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Customer\Model\Session $cusomerSession */
            $cusomerSession = $objectManager->get(\Magento\Customer\Model\Session::class);

            if (!$cusomerSession->isLoggedIn()) {
                $this->joinTable(
                    $select,
                    $tableAlias,
                    $this->resource->getTableName('catalog_product_index_price'),
                    [
                        "{$tableAlias}.entity_id = e.entity_id",
                        "{$tableAlias}.website_id = {$websiteId}",
                        "{$tableAlias}.customer_group_id = 0",
                    ]
                );

                return $tableAlias . '.min_price';
            } else {
                $customerGroupId = $cusomerSession->getCustomerGroupId();

                $tableAliasDefault = $tableAlias . '_0';

                $tableAliasCustomer = $tableAlias . '_' . $customerGroupId;

                $this->joinTable(
                    $select,
                    $tableAliasDefault,
                    $this->resource->getTableName('catalog_product_index_price'),
                    [
                        "{$tableAliasDefault}.entity_id = e.entity_id",
                        "{$tableAliasDefault}.website_id = {$websiteId}",
                        "{$tableAliasDefault}.customer_group_id = 0",
                    ]
                );

                $this->joinTable(
                    $select,
                    $tableAliasCustomer,
                    $this->resource->getTableName('catalog_product_index_price'),
                    [
                        "{$tableAliasCustomer}.entity_id = e.entity_id",
                        "{$tableAliasCustomer}.website_id = {$websiteId}",
                        "{$tableAliasCustomer}.customer_group_id = " . $customerGroupId,
                    ]
                );

                return "IFNULL({$tableAliasCustomer}.min_price, {$tableAliasDefault}.min_price)";
            }
        }

        try {
            $attribute = $this->attributeRepository->get($attributeCode);
        } catch (\Exception$e) {
            return null;
        }

        if ($attribute->getBackend()->isStatic()) {
            return 'e.' . $attributeCode;
        }

        $this->joinTable(
            $select,
            $tableAlias.'_store',
            $attribute->getBackend()->getTable(),
            [
                CompatibilityService::isEnterprise() ? "e.row_id = {$tableAlias}_store.row_id" : "e.entity_id = {$tableAlias}_store.entity_id",
                "{$tableAlias}_store.attribute_id = " . (int)$attribute->getId(),
                "{$tableAlias}_store.store_id = {$storeId}",
            ]
        );

        $this->joinTable(
            $select,
            $tableAlias.'_global',
            $attribute->getBackend()->getTable(),
            [
                CompatibilityService::isEnterprise() ? "e.row_id = {$tableAlias}_global.row_id" : "e.entity_id = {$tableAlias}_global.entity_id",
                "{$tableAlias}_global.attribute_id = " . (int)$attribute->getId(),
                "{$tableAlias}_global.store_id = 0",
            ]
        );

        return 'IFNULL('.$tableAlias . '_store.value, '.$tableAlias.'_global.value)';
    }

    public function addOrder(Select $select, array $expressions, ?string $direction): void
    {
        $expressions = array_filter($expressions);

        if (!count($expressions)) {
            return;
        }

        foreach ($expressions as $key => $expr) {
            if (is_array($expr)) {
                $expressions[$key] = implode(' ', $expr);
            }
        }

        $expressions = implode(' + ', $expressions);

        $select->order(new \Zend_Db_Expr($expressions . ' ' . $direction));
    }

    private function retrieveCategoryIds(Select $select): array
    {
        $categoryIds = [];

        if ($category = $this->registry->registry('current_category')) { // if we on category page
            $categoryIds[] = $category->getId();
        } else { // if we in CMS block or widget where products added by categories
            $where = $select->getPart('where');

            if (!$where) {
                return $categoryIds;
            }

            foreach ($where as $cond) {
                $cond = (string)$cond;

                if (strpos($cond, 'category_id') === false) {
                    continue;
                }

                if (preg_match('/category_id in \(([^)]*)\)/is', $cond, $match)) {
                    $categories = str_replace('\'', '', $match[1]);
                    $categories = str_replace('"', '', $categories);

                    $categoryIds = explode(',', $categories);

                    break;
                }

                if (preg_match('/category_id =[^\d]*(\d*)/is', $cond, $match)) {
                    $categoryIds[] = $match[1];

                    break;
                }
            }
        }

        return $categoryIds;
    }

    private function joinTable(Select $select, string $alias, string $name, array $conditions): void
    {
        foreach ($select->getPart(\Magento\Framework\DB\Select::FROM) as $aliasName => $item) {
            if ($item['tableName'] === $name && $aliasName === $alias) {
                return;
            }
        }

        $select->joinLeft([$alias => $name], implode(' AND ', $conditions), []);
    }
}
