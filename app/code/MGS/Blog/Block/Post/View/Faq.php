<?php

namespace MGS\Blog\Block\Post\View;

use Magento\Framework\View\Element\Template;
use MGS\Blog\Model\Resource\Faq as FaqResource;
use Magento\Framework\Registry;

class Faq extends Template
{
    protected $faqResource;
    protected $registry;
    protected $faqs = null;

    public function __construct(
        Template\Context $context,
        FaqResource $faqResource,
        Registry $registry,
        array $data = []
    ) {
        $this->faqResource = $faqResource;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getFaqs()
    {
        if ($this->faqs !== null) {
            return $this->faqs;
        }

        $post = $this->registry->registry('current_post');
        if (!$post || !$post->getId()) {
            $this->faqs = [];
            return $this->faqs;
        }

        $postId = $post->getId();
        $storeId = $this->_storeManager->getStore()->getId();
        $connection = $this->faqResource->getConnection();
        $table = $this->faqResource->getMainTable();

        // Try store-specific FAQs first
        $select = $connection->select()
            ->from($table)
            ->where('post_id = ?', (int)$postId)
            ->where('store_id = ?', (int)$storeId)
            ->order('sort_order ASC');

        $results = $connection->fetchAll($select);

        // Fallback to store_id = 0 (all stores)
        if (empty($results)) {
            $select = $connection->select()
                ->from($table)
                ->where('post_id = ?', (int)$postId)
                ->where('store_id = ?', 0)
                ->order('sort_order ASC');

            $results = $connection->fetchAll($select);
        }

        $this->faqs = $results;
        return $this->faqs;
    }

    public function hasFaqs()
    {
        return !empty($this->getFaqs());
    }

    public function getJsonLdData()
    {
        $faqs = $this->getFaqs();
        if (empty($faqs)) {
            return null;
        }

        $mainEntity = [];
        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['title'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['description']
                ]
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
