<?php

namespace Ecomteck\StorePickup\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class InstallerBlock extends Template
{
    const CONFIG_PATH_DISPLAY_PAGE = 'ecomteck_storelocator/installer/display_page';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * Only render on cart page if config = shopping_cart
     */
    protected function _toHtml()
    {
        $displayPage = $this->scopeConfig->getValue(
            self::CONFIG_PATH_DISPLAY_PAGE,
            ScopeInterface::SCOPE_STORE
        );

        if ($displayPage === 'reference_cart') {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get the configured display page value
     */
    public function getDisplayPage()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_DISPLAY_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
