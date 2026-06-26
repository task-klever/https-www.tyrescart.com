<?php

namespace Hdweb\Purchaseorder\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'vatApplicable',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 1,
                    'comment'  => 'VAT Applicable',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'created_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment'  => 'Vendor Created At',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'create_by',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Created By',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'update_by',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Updated By',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'created_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment'  => 'Created Date',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'updated_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment'  => 'Updated Date',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'email_copy',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Vendor Send copy mail',
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'po_grandtotal',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'po_grandtotal',
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'po_margin',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'po_margin',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'po_marginperc',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'po_marginperc',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'vendor_id',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'vendor_id',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'vendor_name',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'vendor_name',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'order_id',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'order_id',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'created_at',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'created_at',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order_item'),
                'tyre_description',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'tyre_description ',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('po_vendor_fitment'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'vendor_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'NULL'],
                    'Vendor ID'
                )->addColumn(
                    'sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'NULL'],
                    'SKU'
                )->addColumn(
                    'vendor_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'NULL'],
                    'Vendor Price'
                )->addColumn(
                    'comment',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'NULL'],
                    'Comment'
                )->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'NULL'],
                    'Status'
                )->setComment(
                    'Po Vendor Fitment Table'
                );

            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'rnr_purchase_order_response',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'RNR Purchaseorder Response',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'po_type',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Purchase Order Type',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('purchase_order'),
                'date',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Date',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'pickup_store',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Pick Up Store',
                ]
            );
        }
		 if (version_compare($context->getVersion(), '1.0.10') < 0) {
			$installer->getConnection()->addColumn(
				$installer->getTable('purchase_order'),
				'vendor_name',
				[
					'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'default'  => '',
					'comment'  => 'vendor_name',
				]
			);
		 }
		 if (version_compare($context->getVersion(), '1.0.11') < 0) {
			$installer->getConnection()->addColumn(
				$installer->getTable('purchase_order_item'),
				'poreference_no',
				[
					'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'default'  => '',
					'comment'  => 'PO Reference No',
				]
			);
		 }
		 if (version_compare($context->getVersion(), '1.0.12') < 0) {
			$installer->getConnection()->addColumn(
				$installer->getTable('purchase_order_item'),
				'po_type',
				[
					'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'default'  => '',
					'comment'  => 'PO Type',
				]
			);
		 }

         if (version_compare($context->getVersion(), '1.0.13') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('po_vendor'),
                'whatsapp_number',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Whatsapp Number',
                ]
            );
        }

        $installer->endSetup();
    }

}
