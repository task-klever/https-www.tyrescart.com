<?php

namespace MGS\Brand\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('mgs_brand'),
                'brand_category',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment'  => 'Brand Category',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $table = $installer->getConnection()->newTable($installer->getTable('mgs_brand_patternmanagement'))
                    ->addColumn('patternmanagement_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true])
                    ->addColumn('brand_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('brand', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
                    ->addColumn('pattern_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('pattern', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
                    ->addColumn('image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
                    ->addColumn('short_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
                    ->addColumn('description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
                    ->addColumn('performance_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
                    ->addColumn('dry', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('wet', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('sport', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('comfort', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('mileage', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('url_key', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [])
                    ->addColumn('meta_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
                    ->addColumn('meta_keywords', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [])
                    ->addColumn('meta_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
                    ->addColumn('youtube_video_link', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [])
                    ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1])
                    ->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created At')
                    ->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                    ->setComment('Mageplaza Shopbybrand Pattern table');

                $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('mgs_brand_patternmanagement'),
                'store_id',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'comment'  => 'Store Id',
                ]
            );
        }

        $installer->endSetup();
    }

}
