<?php

namespace Klever\Faq\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Mageprince\Faq\Model\Config\DefaultConfig;
use Mageprince\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Mageprince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory as FaqGroupCollectionFactory;
use Mageprince\Faq\Helper\Data as HelperData;
use Magento\Cms\Model\Template\FilterProvider;

class FooterFaq extends Template
{
    private CollectionFactory $faqCollectionFactory;
    private FaqGroupCollectionFactory $faqGroupCollectionFactory;
    private HelperData $helper;
    private FilterProvider $filterProvider;

    public function __construct(
        Template\Context $context,
        CollectionFactory $faqCollectionFactory,
        FaqGroupCollectionFactory $faqGroupCollectionFactory,
        FilterProvider $filterProvider,
        HelperData $helper,
        array $data = []
    ) {
        $this->faqCollectionFactory = $faqCollectionFactory;
        $this->faqGroupCollectionFactory = $faqGroupCollectionFactory;
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * Disable block caching so FAQ renders per-page
     */
    protected function getCacheLifetime(): ?int
    {
        return null;
    }

    /**
     * Get current page slug (without store code prefix)
     */
    public function getCurrentSlug(): string
    {
        $url = $this->getRequest()->getOriginalPathInfo();
        if (!$url) {
            $url = $this->getRequest()->getPathInfo();
        }
        $slug = trim($url, '/');

        // Remove store code prefix (e.g., "en/", "ar/")
        try {
            $storeCode = $this->_storeManager->getStore()->getCode();
            if (strpos($slug, $storeCode . '/') === 0) {
                $slug = substr($slug, strlen($storeCode) + 1);
            } elseif ($slug === $storeCode) {
                $slug = 'home';
            }
        } catch (\Exception $e) {
            // ignore
        }

        $slug = preg_replace('/\.html$/', '', $slug);

        if ($slug === '' || $slug === false) {
            $slug = 'home';
        }

        return $slug;
    }

    /**
     * Get FAQ groups that should display on the current page footer
     * Reads footer_slugs column from prince_faqgroup table
     */
    public function getMatchingFaqGroups(): array
    {
        $currentSlug = $this->getCurrentSlug();
        $storeId = $this->_storeManager->getStore()->getId();

        $collection = $this->faqGroupCollectionFactory->create();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('footer_slugs', ['notnull' => true]);
        $collection->addFieldToFilter('footer_slugs', ['neq' => '']);
        $collection->addFieldToFilter(
            'storeview',
            [
                ['eq' => 0],
                ['finset' => $storeId]
            ]
        );
        $collection->setOrder('sortorder', 'ASC');

        $matchedGroups = [];
        foreach ($collection as $group) {
            $slugs = array_map('trim', explode(',', $group->getData('footer_slugs')));
            if (in_array($currentSlug, $slugs)) {
                $matchedGroups[] = $group;
            }
        }

        return $matchedGroups;
    }

    /**
     * Check if FAQ should show on current page
     */
    public function shouldShow(): bool
    {
        if (!$this->helper->isEnable()) {
            return false;
        }

        return !empty($this->getMatchingFaqGroups());
    }

    /**
     * Get FAQ items for a group
     */
    public function getFaqCollection(int $groupId)
    {
        $collection = $this->faqCollectionFactory->create();
        $collection->addFieldToFilter(
            'group',
            [
                ['null' => true],
                ['finset' => $groupId]
            ]
        );
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter(
            'customer_group',
            [
                ['null' => true],
                ['finset' => $this->helper->getCustomerGroupId()]
            ]
        );
        $collection->addFieldToFilter(
            'storeview',
            [
                ['eq' => 0],
                ['finset' => $this->_storeManager->getStore()->getId()]
            ]
        );
        $collection->setOrder('sortorder', 'ASC');
        return $collection;
    }

    /**
     * Filter FAQ content HTML
     */
    public function filterOutputHtml(string $string): string
    {
        try {
            return $this->filterProvider->getPageFilter()->filter($string);
        } catch (\Exception $e) {
            return $string;
        }
    }
}
