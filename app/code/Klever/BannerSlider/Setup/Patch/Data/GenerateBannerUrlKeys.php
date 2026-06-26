<?php

namespace Klever\BannerSlider\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\App\ResourceConnection;

class GenerateBannerUrlKeys implements DataPatchInterface
{
    private ResourceConnection $resourceConnection;
    private TranslitUrl $translitUrl;

    public function __construct(
        ResourceConnection $resourceConnection,
        TranslitUrl $translitUrl
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->translitUrl = $translitUrl;
    }

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('mageplaza_bannerslider_banner');

        $banners = $connection->fetchAll(
            $connection->select()
                ->from($table, ['banner_id', 'name'])
                ->where('url_key IS NULL OR url_key = ?', '')
        );

        foreach ($banners as $banner) {
            $urlKey = $this->generateUrlKey($banner['name']);
            $urlKey = $this->ensureUnique($connection, $table, (int)$banner['banner_id'], $urlKey);
            $connection->update(
                $table,
                ['url_key' => $urlKey],
                ['banner_id = ?' => $banner['banner_id']]
            );
        }

        return $this;
    }

    private function generateUrlKey(string $text): string
    {
        $urlKey = $this->translitUrl->filter($text);
        return strtolower(trim($urlKey, '-'));
    }

    private function ensureUnique($connection, string $table, int $currentId, string $urlKey): string
    {
        $baseUrlKey = $urlKey;
        $counter = 1;
        while (true) {
            $existing = $connection->fetchOne(
                $connection->select()
                    ->from($table, ['banner_id'])
                    ->where('url_key = ?', $urlKey)
                    ->where('banner_id != ?', $currentId)
                    ->limit(1)
            );
            if (!$existing) {
                return $urlKey;
            }
            $urlKey = $baseUrlKey . '-' . $counter;
            $counter++;
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
