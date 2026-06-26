<?php

namespace Hdweb\Brandoveride\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomBrandFields implements DataPatchInterface
{
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $tableName = $this->moduleDataSetup->getTable('mgs_brand'); // original table

        $columns = [
            'tab1_title' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 1 Title'],
            'tab1_description' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 1 Description'],
            'tab2_title' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 2 Title'],
            'tab2_description' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 2 Description'],
            'tab3_title' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 3 Title'],
            'tab3_description' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 3 Description'],
            'tab4_title' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 4 Title'],
            'tab4_description' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 4 Description'],
            'tab5_title' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 5 Title'],
            'tab5_description' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'Tab 5 Description'],
            'topbanner_image' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => 'Top Banner Image'],
            'bottombanner_image' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => 'Bottom Banner Image'],
        ];

        $connection = $this->moduleDataSetup->getConnection();

        foreach ($columns as $name => $definition) {
            if (!$connection->tableColumnExists($tableName, $name)) {
                $connection->addColumn($tableName, $name, $definition);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
