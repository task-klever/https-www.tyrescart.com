<?php

namespace Klever\Faq\Plugin;

use MGS\Blog\Controller\Router as BlogRouter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Mageprince\Faq\Model\Config\DefaultConfig;

class SkipBlogRouterForFaqUrls
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Prevent MGS Blog router from matching URLs that belong to FAQ
     * (e.g. /faqs/tyres, /faqs/tyres/some-question)
     */
    public function aroundMatch(
        BlogRouter $subject,
        callable $proceed,
        RequestInterface $request
    ): ?ActionInterface {
        $identifier = trim($request->getPathInfo(), '/');
        $faqUrl = $this->scopeConfig->getValue(DefaultConfig::FAQ_URL_CONFIG_PATH) ?: 'faq';

        // If URL starts with FAQ prefix, skip blog router entirely
        if ($identifier === $faqUrl || strpos($identifier, $faqUrl . '/') === 0) {
            return null;
        }

        return $proceed($request);
    }
}
