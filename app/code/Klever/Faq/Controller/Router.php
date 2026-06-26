<?php

namespace Klever\Faq\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Store\Model\ScopeInterface;
use Mageprince\Faq\Model\Config\DefaultConfig;
use Mageprince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory as FaqGroupCollectionFactory;
use Mageprince\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;

class Router implements RouterInterface
{
    private ActionFactory $actionFactory;
    private ScopeConfigInterface $scopeConfig;
    private FaqGroupCollectionFactory $faqGroupCollectionFactory;
    private FaqCollectionFactory $faqCollectionFactory;

    public function __construct(
        ActionFactory $actionFactory,
        ScopeConfigInterface $scopeConfig,
        FaqGroupCollectionFactory $faqGroupCollectionFactory,
        FaqCollectionFactory $faqCollectionFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->faqGroupCollectionFactory = $faqGroupCollectionFactory;
        $this->faqCollectionFactory = $faqCollectionFactory;
    }

    /**
     * Match FAQ URLs:
     *   /faqs              → main FAQ page (handled by Mageprince router)
     *   /faqs/{group-slug} → group detail page
     *   /faqs/{group-slug}/{faq-slug} → single FAQ detail page
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $isModuleEnabled = $this->scopeConfig->getValue(
            DefaultConfig::CONFIG_PATH_IS_ENABLE,
            ScopeInterface::SCOPE_STORE
        );

        if (!$isModuleEnabled) {
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $faqUrl = $this->scopeConfig->getValue(DefaultConfig::FAQ_URL_CONFIG_PATH);

        if (!$faqUrl) {
            $faqUrl = 'faq';
        }

        // Only handle paths that start with the FAQ base URL and have extra segments
        if (strpos($identifier, $faqUrl . '/') !== 0) {
            return null;
        }

        // Remove the base FAQ URL prefix to get the remaining path
        $remainingPath = substr($identifier, strlen($faqUrl) + 1);
        $segments = explode('/', $remainingPath);

        // Filter out empty segments
        $segments = array_values(array_filter($segments, function ($s) {
            return $s !== '';
        }));

        if (empty($segments)) {
            return null;
        }

        $groupSlug = $segments[0];
        $faqSlug = isset($segments[1]) ? implode('/', array_slice($segments, 1)) : null;

        // Look up the group by url_key
        $groupCollection = $this->faqGroupCollectionFactory->create();
        $groupCollection->addFieldToFilter('url_key', $groupSlug);
        $groupCollection->addFieldToFilter('status', 1);
        $group = $groupCollection->getFirstItem();

        if (!$group->getId()) {
            return null;
        }

        if ($faqSlug !== null) {
            // Single FAQ detail page: /faqs/{group-slug}/{faq-slug}
            $faqCollection = $this->faqCollectionFactory->create();
            $faqCollection->addFieldToFilter('url_key', $faqSlug);
            $faqCollection->addFieldToFilter('status', 1);
            $faqCollection->addFieldToFilter(
                'group',
                [
                    ['null' => true],
                    ['finset' => $group->getId()]
                ]
            );
            $faq = $faqCollection->getFirstItem();

            if (!$faq->getId()) {
                return null;
            }

            $request->setModuleName('kleverfaq');
            $request->setControllerName('faq');
            $request->setActionName('view');
            $request->setParam('faq_id', $faq->getId());
            $request->setParam('group_id', $group->getId());
        } else {
            // Group detail page: /faqs/{group-slug}
            $request->setModuleName('kleverfaq');
            $request->setControllerName('group');
            $request->setActionName('view');
            $request->setParam('group_id', $group->getId());
        }

        return $this->actionFactory->create(Forward::class);
    }
}
