<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

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
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'pickup_date',
            [
                'type' => 'datetime',
                'nullable' => true,
                'comment' => 'Pick Up Date',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'pickup_time',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Pick Up Time',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'pickup_store',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Pick Up Store',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'pickup_date',
            [
                'type' => 'datetime',
                'nullable' => true,
                'comment' => 'Pick Up Date',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'pickup_time',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Pick Up Time',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'pickup_store',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Pick Up Store',
            ]
        );

        $setup->endSetup();
    }
}