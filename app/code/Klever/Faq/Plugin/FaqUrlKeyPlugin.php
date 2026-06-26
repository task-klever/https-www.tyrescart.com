<?php

namespace Klever\Faq\Plugin;

use Mageprince\Faq\Api\FaqRepositoryInterface;
use Mageprince\Faq\Api\Data\FaqInterface;
use Magento\Framework\Filter\TranslitUrl;

class FaqUrlKeyPlugin
{
    private TranslitUrl $translitUrl;

    public function __construct(TranslitUrl $translitUrl)
    {
        $this->translitUrl = $translitUrl;
    }

    /**
     * Auto-generate url_key from FAQ title before saving if url_key is empty
     */
    public function beforeSave(
        FaqRepositoryInterface $subject,
        FaqInterface $faq
    ): array {
        $urlKey = $faq->getData('url_key');

        if (empty($urlKey)) {
            $title = $faq->getTitle();
            if ($title) {
                $urlKey = $this->generateUrlKey($title);
                $faq->setData('url_key', $urlKey);
            }
        } else {
            // Sanitize user-provided url_key
            $urlKey = $this->generateUrlKey($urlKey);
            $faq->setData('url_key', $urlKey);
        }

        return [$faq];
    }

    private function generateUrlKey(string $text): string
    {
        $urlKey = $this->translitUrl->filter($text);
        $urlKey = strtolower(trim($urlKey, '-'));
        return $urlKey;
    }
}
