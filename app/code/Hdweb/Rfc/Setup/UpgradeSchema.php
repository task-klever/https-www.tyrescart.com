<?php

namespace Hdweb\Rfc\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $table = $installer->getTable('rfc');
            $columns = [
                'rfc_ip_address' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'RFC IP ADDRESS',
                ],
            ];
            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($table, $name, $definition);
            }
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {
			$table = $installer->getTable('quote');
			$columns = [
                'abd_cron_mail_date' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Abandoned Cart Mail Sent Status',
                ],
				'abd_cron_mail_status' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Abandoned Cart Mail Status',
                ]
            ];
			
            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($table, $name, $definition);
            }
        }

        $installer->endSetup();
    }

}
