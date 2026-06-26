<?php

namespace Klever\Faq\Block;

use Magento\Framework\View\Element\Template;
use Mageprince\Faq\Api\FaqRepositoryInterface;
use Mageprince\Faq\Api\FaqGroupRepositoryInterface;
use Mageprince\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Mageprince\Faq\Helper\Data as HelperData;
use Magento\Cms\Model\Template\FilterProvider;
use Mageprince\Faq\Model\Config\DefaultConfig;
use Magento\Framework\UrlInterface;

class FaqView extends Template
{
    private FaqRepositoryInterface $faqRepository;
    private FaqGroupRepositoryInterface $faqGroupRepository;
    private CollectionFactory $faqCollectionFactory;
    private HelperData $helper;
    private FilterProvider $filterProvider;

    public function __construct(
        Template\Context $context,
        FaqRepositoryInterface $faqRepository,
        FaqGroupRepositoryInterface $faqGroupRepository,
        CollectionFactory $faqCollectionFactory,
        FilterProvider $filterProvider,
        HelperData $helper,
        array $data = []
    ) {
        $this->faqRepository = $faqRepository;
        $this->faqGroupRepository = $faqGroupRepository;
        $this->faqCollectionFactory = $faqCollectionFactory;
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function getFaqId(): int
    {
        return (int)$this->getRequest()->getParam('faq_id');
    }

    public function getGroupId(): int
    {
        return (int)$this->getRequest()->getParam('group_id');
    }

    public function getFaq()
    {
        try {
            return $this->faqRepository->getById($this->getFaqId());
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getGroup()
    {
        try {
            return $this->faqGroupRepository->getById($this->getGroupId());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get other FAQs in the same group (for sidebar/related)
     */
    public function getRelatedFaqs()
    {
        $collection = $this->faqCollectionFactory->create();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter(
            'group',
            [
                ['null' => true],
                ['finset' => $this->getGroupId()]
            ]
        );
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

    public function filterOutputHtml(string $string): string
    {
        try {
            return $this->filterProvider->getPageFilter()->filter($string);
        } catch (\Exception $e) {
            return $string;
        }
    }

    public function getFaqBaseUrl(): string
    {
        $faqUrl = $this->_scopeConfig->getValue(DefaultConfig::FAQ_URL_CONFIG_PATH);
        if (!$faqUrl) {
            $faqUrl = 'faq';
        }
        return $this->getBaseUrl() . $faqUrl;
    }

    public function getGroupUrl(): string
    {
        $group = $this->getGroup();
        if (!$group) {
            return '#';
        }
        $faqUrl = $this->_scopeConfig->getValue(DefaultConfig::FAQ_URL_CONFIG_PATH);
        if (!$faqUrl) {
            $faqUrl = 'faq';
        }
        $groupSlug = $group->getData('url_key');
        if (!$groupSlug) {
            return '#';
        }
        return $this->getBaseUrl() . $faqUrl . '/' . $groupSlug;
    }

    public function getFaqDetailUrl($faq): string
    {
        $group = $this->getGroup();
        if (!$group) {
            return '#';
        }
        $faqUrl = $this->_scopeConfig->getValue(DefaultConfig::FAQ_URL_CONFIG_PATH);
        if (!$faqUrl) {
            $faqUrl = 'faq';
        }
        $groupSlug = $group->getData('url_key');
        $faqSlug = $faq->getData('url_key');
        if (!$groupSlug || !$faqSlug) {
            return '#';
        }
        return $this->getBaseUrl() . $faqUrl . '/' . $groupSlug . '/' . $faqSlug;
    }

    public function getGroupImageUrl(?string $icon): string
    {
        if (!$icon) {
            return '';
        }
        try {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . DefaultConfig::ICON_TMP_PATH . $icon;
        } catch (\Exception $e) {
            return '';
        }
    }
}
