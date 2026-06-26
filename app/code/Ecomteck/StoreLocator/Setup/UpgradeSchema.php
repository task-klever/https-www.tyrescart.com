<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */

namespace Ecomteck\StoreLocator\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */

    // @codingStandardsIgnoreStart
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if ($installer->tableExists('ecomteck_storelocator_stores')) {
            $table = $installer->getTable('ecomteck_storelocator_stores');
            $connection = $installer->getConnection();
            if (version_compare($context->getVersion(), '2.0.0') < 0) {
                $connection->addColumn(
                    $table,
                    'station',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Nearest Station'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'description',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Description'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'intro',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Intro'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'details_image',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Details Image'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'distance',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Distance'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'external_link',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'External Link'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.0.1') < 0) {
                $connection->addColumn(
                    $table,
                    'opening_hours',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Opening Hours'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'special_opening_hours',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Special Opening Hours'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.0.2') < 0) {
                // Create table for featured and additional store products
                $table_name = 'ecomteck_storelocator_products';

                $storelocator_products_table = $connection->newTable($installer->getTable($table_name))
                    ->addColumn('stores_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['nullable' => false, 'unsigned' => true, 'primary' => true],
                                'Store ID')
                    ->addColumn('product_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['nullable' => false, 'unsigned' => true, 'primary' => true],
                                'Product ID')
                    ->addColumn('position',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['nullable' => false, 'default' => '0'],
                                'Position')
                    ->addIndex($installer->getIdxName($table_name,'product_id'),'product_id')
                    ->addForeignKey($setup->getFkName($table_name,'stores_id','ecomteck_storelocator_stores','stores_id'),
                                    'stores_id',
                                    $installer->getTable('ecomteck_storelocator_stores'),
                                    'stores_id',
                                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
                    ->addForeignKey($installer->getFkName($table_name,'product_id','catalog_product_entity','entity_id'),
                                    'product_id',
                                    $installer->getTable('catalog_product_entity'),
                                    'entity_id',
                                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
                    ->setComment('Catalog Product To Store Linkage Table');

                    $connection->createTable($storelocator_products_table);
            }


            if (version_compare($context->getVersion(), '2.0.3') < 0) {
                $connection->addColumn(
                    $table,
                    'category',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Category'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.0.4') < 0) {
                $connection->addColumn(
                    $table,
                    'is_all_products',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'length' => null,
                        'nullable' => true,
                        'default'   => '1',
                        'comment' => 'All Products Available For This Store'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.0.5') < 0) {
                $connection->addColumn(
                    $table,
                    'ismobilevan',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Is Mobile Van'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'shipping_amount',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Shipping Amount'
                    ]
                );
                $connection->addColumn(
                    $table,
                    'installer_sort_order',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'length' => 10,
                        'nullable' => true,
                        'comment' => 'Installer Sort Order'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.0.6') < 0) {
                $connection->addColumn(
                    $table,
                    'google_map',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Google Map'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'service_included',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => null,
                        'nullable' => true,
                        'comment' => 'Service Included'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'opening_hours_one',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Opening Hours One'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'opening_hours_two',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Opening Hours Two'
                    ]
                );
            }

             if (version_compare($context->getVersion(), '2.0.7') < 0) {
                $connection->addColumn(
                    $table,
                    'skip_days',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Skip Days'
                    ]
                );
            }

              if (version_compare($context->getVersion(), '2.0.8') < 0) {
                $connection->addColumn(
                    $table,
                    'comming_soon',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Comming Soon'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.0.9') < 0) {
                $connection->addColumn(
                    $table,
                    'image_1',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Image 1'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'image_2',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Image 2'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'image_3',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Image 3'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'image_4',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Image 4'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'image_5',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Image 5'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.1.0') < 0) {
                $connection->addColumn(
                    $table,
                    'cutoff_time',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 10,
                        'nullable' => true,
                        'comment' => 'Cutoff Time'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'skip_hours',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'length' => 10,
                        'nullable' => true,
                        'comment' => 'Skip Hours'
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.1.1') < 0) {
                $connection->addColumn(
                    $table,
                    'name_ar',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Name Arabic'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'city_ar',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'City Arabic'
                    ]
                );

                $connection->addColumn(
                    $table,
                    'address_ar',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Address Arabic'
                    ]
                );
            }

            $installer->endSetup();
        }
    }
}