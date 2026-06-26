<?php

namespace TotalPay\Gateway\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class IframeConfigProvider implements ConfigProviderInterface
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    public function getConfig()
    {
        return [
            'payment' => [
                'totalpay_checkout' => [
                    'enable_iframe' => $this->scopeConfig->getValue('payment/totalpay_checkout/iframe_enable', ScopeInterface::SCOPE_STORE),
                ]
            ]
        ];
    }
}
