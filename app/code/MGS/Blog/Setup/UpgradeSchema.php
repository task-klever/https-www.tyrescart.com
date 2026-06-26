<?php

namespace MGS\Blog\Setup;

use Magento\Backend\Block\Widget\Tab;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Declaration\Schema\Operations\AddColumn;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.7') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('mgs_blog_post'),
                'published_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'comment' => 'Published At',
                ]
            );
        }

        $intaller = $setup;
        $intaller->startSetup();
        if (version_compare($context->getVersion(), '2.2.1') < 0 ) {
             $table = $intaller->getConnection()
                ->newTable($intaller->getTable('mgs_blog_post_update'))
                ->addColumn(
                    'post_id',
                     Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Post Id'
                )
                ->addColumn(
                    'scope',
                   Table::TYPE_TEXT,
                    null,
                    ['nullable'=>false],
                    'Scope'
                )
                ->addColumn(
                    'scope_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable'=>false],
                    'Scope Id'
                )
                ->addColumn(
                    'field',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable'=>false],
                    'Field'
                )
                ->addColumn(
                    'value',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable'=>false],
                    'Value'
                )
                ->addForeignKey(
                    $intaller->getFkName('mgs_blog_post_update', 'post_id', 'mgs_blog_post', 'post_id'),
                    'post_id',
                    $intaller->getTable('mgs_blog_post'),
                    'post_id',
                    Table::ACTION_CASCADE
                )
                
                ->setComment('Blog Posts Update');
            $intaller->getConnection()->createTable($table);

            $table = $intaller->getConnection()
            ->newTable($intaller->getTable('mgs_blog_post_update'))
            ->addColumn(
                'post_id',
                 Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Post Id'
            )
            ->addColumn(
                'scope',
               Table::TYPE_TEXT,
                null,
                ['nullable'=>false],
                'Scope'
            )
            ->addColumn(
                'scope_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable'=>false],
                'Scope Id'
            )
            ->addColumn(
                'field',
                Table::TYPE_TEXT,
                null,
                ['nullable'=>false],
                'Field'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                ['nullable'=>false],
                'Value'
            )
            ->addForeignKey(
                $intaller->getFkName('mgs_blog_post_update', 'post_id', 'mgs_blog_post', 'post_id'),
                'post_id',
                $intaller->getTable('mgs_blog_post'),
                'post_id',
                Table::ACTION_CASCADE
            )
            
            ->setComment('Blog Posts Update');
        $intaller->getConnection()->createTable($table);
        
        $table = $intaller->getConnection()
        ->newTable($intaller->getTable('mgs_blog_category_update'))
        ->addColumn(
            'category_id',
             Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Category Id'
        )
        ->addColumn(
            'scope',
           Table::TYPE_TEXT,
            null,
            ['nullable'=>false],
            'Scope'
        )
        ->addColumn(
            'scope_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable'=>false],
            'Scope Id'
        )
        ->addColumn(
            'field',
            Table::TYPE_TEXT,
            null,
            ['nullable'=>false],
            'Field'
        )
        ->addColumn(
            'value',
            Table::TYPE_TEXT,
            null,
            ['nullable'=>false],
            'Value'
        )
        ->addForeignKey(
            $intaller->getFkName('mgs_blog_category_update', 'category_id', 'mgs_blog_category', 'category_id'),
            'category_id',
            $intaller->getTable('mgs_blog_category'),
            'category_id',
            Table::ACTION_CASCADE
        )
        
        ->setComment('Blog Posts Update');
    $intaller->getConnection()->createTable($table);
    
            
            $intaller->endSetup();
        }

        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('mgs_blog_post'),
                'webp_image',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Webp Image',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.2.1') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('mgs_blog_tag'),
                'tag_name',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'comment' => 'Tag Name',
                ]
            );
            
            $connection->addColumn(
                $setup->getTable('mgs_blog_tag'),
                'short_description',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Short Description',
                ]
            );

            $connection->addColumn(
                $setup->getTable('mgs_blog_tag'),
                'long_description',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Long Description',
                ]
            );

            $connection->addColumn(
                $setup->getTable('mgs_blog_tag'),
                'meta_title',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Meta Title',
                ]
            );

            $connection->addColumn(
                $setup->getTable('mgs_blog_tag'),
                'meta_description',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Meta Description',
                ]
            );
        }


        if (version_compare($context->getVersion(), '2.2.3') < 0) {
            $connection = $setup->getConnection();
          

            $connection->addColumn(
                $setup->getTable('mgs_blog_post'),
                'categories',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'categories',
                ]
            );
        }
        
    }
}
