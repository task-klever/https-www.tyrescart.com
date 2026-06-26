<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\CustomerCustomAttributes\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'magento_customercustomattributes_sales_flat_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_customercustomattributes_sales_flat_order')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
            'Entity Id'
        )->addForeignKey(
            $installer->getFkName(
                'magento_customercustomattributes_sales_flat_order',
                'entity_id',
                'sales_order',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            ' Customer Sales Flat Order'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'magento_customercustomattributes_sales_flat_order_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_customercustomattributes_sales_flat_order_address')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
            'Entity Id'
        )->addForeignKey(
            $installer->getFkName(
                'magento_customercustomattributes_sales_flat_order_address',
                'entity_id',
                'sales_order_address',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('sales_order_address'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            ' Customer Sales Flat Order Address'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'magento_customercustomattributes_sales_flat_quote'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_customercustomattributes_sales_flat_quote')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
            'Entity Id'
        )->addForeignKey(
            $installer->getFkName(
                'magento_customercustomattributes_sales_flat_quote',
                'entity_id',
                'quote',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            ' Customer Sales Flat Quote'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'magento_customercustomattributes_sales_flat_quote_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_customercustomattributes_sales_flat_quote_address')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
            'Entity Id'
        )->addForeignKey(
            $installer->getFkName(
                'magento_customercustomattributes_sales_flat_quote_address',
                'entity_id',
                'quote_address',
                'address_id'
            ),
            'entity_id',
            $installer->getTable('quote_address'),
            'address_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            ' Customer Sales Flat Quote Address'
        );
        $installer->getConnection()->createTable($table);

    }
}
