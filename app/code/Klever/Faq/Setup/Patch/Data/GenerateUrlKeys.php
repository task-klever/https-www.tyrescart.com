<?php

namespace Klever\Faq\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\App\ResourceConnection;

class GenerateUrlKeys implements DataPatchInterface
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

        // Generate url_key for FAQ groups that don't have one
        $groupTable = $this->resourceConnection->getTableName('prince_faqgroup');
        $groups = $connection->fetchAll(
            $connection->select()
                ->from($groupTable, ['faqgroup_id', 'groupname'])
                ->where('url_key IS NULL OR url_key = ?', '')
        );

        foreach ($groups as $group) {
            $urlKey = $this->generateUrlKey($group['groupname']);
            $urlKey = $this->ensureUniqueUrlKey($connection, $groupTable, 'faqgroup_id', $group['faqgroup_id'], $urlKey);
            $connection->update(
                $groupTable,
                ['url_key' => $urlKey],
                ['faqgroup_id = ?' => $group['faqgroup_id']]
            );
        }

        // Generate url_key for FAQ items that don't have one
        $faqTable = $this->resourceConnection->getTableName('prince_faq');
        $faqs = $connection->fetchAll(
            $connection->select()
                ->from($faqTable, ['faq_id', 'title'])
                ->where('url_key IS NULL OR url_key = ?', '')
        );

        foreach ($faqs as $faq) {
            $urlKey = $this->generateUrlKey($faq['title']);
            $urlKey = $this->ensureUniqueUrlKey($connection, $faqTable, 'faq_id', $faq['faq_id'], $urlKey);
            $connection->update(
                $faqTable,
                ['url_key' => $urlKey],
                ['faq_id = ?' => $faq['faq_id']]
            );
        }

        return $this;
    }

    private function generateUrlKey(string $text): string
    {
        $urlKey = $this->translitUrl->filter($text);
        return strtolower(trim($urlKey, '-'));
    }

    /**
     * Ensure url_key is unique within the table by appending -1, -2, etc.
     */
    private function ensureUniqueUrlKey($connection, string $table, string $idColumn, int $currentId, string $urlKey): string
    {
        $baseUrlKey = $urlKey;
        $counter = 1;

        while (true) {
            $existing = $connection->fetchOne(
                $connection->select()
                    ->from($table, [$idColumn])
                    ->where('url_key = ?', $urlKey)
                    ->where($idColumn . ' != ?', $currentId)
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
