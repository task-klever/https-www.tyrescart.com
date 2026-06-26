<?php

namespace Hdweb\Specialoffers\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $salesruleTable = 'salesrule';

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($salesruleTable),
                'rule_banner',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Cart Rule Banner Image Path'
                ]
            );

             
        $setup->getConnection()
        ->addColumn(
            $setup->getTable($salesruleTable),
            'color_text',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' =>'Color'
            ]
        );
        
     
        $setup->endSetup();
    }
}