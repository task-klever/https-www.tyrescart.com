<?php
namespace WeltPixel\GA4\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class MoveTikTokOptionsToNewSection implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $coreConfigDataTableName = $this->moduleDataSetup->getTable('core_config_data');
        $updateQuery = "
        UPDATE $coreConfigDataTableName
        SET path = REPLACE(path, 'weltpixel_ga4/tiktok_pixel_tracking/', 'weltpixel_ga4_tiktok_pixel/general_tracking/')
        WHERE path LIKE '%weltpixel_ga4/tiktok_pixel_tracking/%';
        ";
        $this->moduleDataSetup->getConnection()->query($updateQuery);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.3';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
