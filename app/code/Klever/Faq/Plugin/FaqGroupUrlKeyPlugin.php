<?php

namespace Klever\Faq\Plugin;

use Mageprince\Faq\Api\FaqGroupRepositoryInterface;
use Mageprince\Faq\Api\Data\FaqGroupInterface;
use Magento\Framework\Filter\TranslitUrl;

class FaqGroupUrlKeyPlugin
{
    private TranslitUrl $translitUrl;

    public function __construct(TranslitUrl $translitUrl)
    {
        $this->translitUrl = $translitUrl;
    }

    /**
     * Auto-generate url_key from group name before saving if url_key is empty
     */
    public function beforeSave(
        FaqGroupRepositoryInterface $subject,
        FaqGroupInterface $faqGroup
    ): array {
        $urlKey = $faqGroup->getData('url_key');

        if (empty($urlKey)) {
            $groupName = $faqGroup->getGroupName();
            if ($groupName) {
                $urlKey = $this->generateUrlKey($groupName);
                $faqGroup->setData('url_key', $urlKey);
            }
        } else {
            // Sanitize user-provided url_key
            $urlKey = $this->generateUrlKey($urlKey);
            $faqGroup->setData('url_key', $urlKey);
        }

        return [$faqGroup];
    }

    private function generateUrlKey(string $text): string
    {
        // Use Magento's transliteration to convert to URL-safe string
        $urlKey = $this->translitUrl->filter($text);
        // Ensure lowercase, trim dashes
        $urlKey = strtolower(trim($urlKey, '-'));
        return $urlKey;
    }
}
