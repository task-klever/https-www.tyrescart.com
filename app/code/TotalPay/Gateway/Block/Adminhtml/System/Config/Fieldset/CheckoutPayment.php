<?php

namespace TotalPay\Gateway\Block\Adminhtml\System\Config\Fieldset;

/**
 * Renderer for TotalPay Checkout Panel in System Configuration
 *
 * Class CheckoutPayment
 * @package TotalPay\Gateway\Block\Adminhtml\System\Config\Fieldset
 */
class CheckoutPayment extends \TotalPay\Gateway\Block\Adminhtml\System\Config\Fieldset\Base\Payment
{
    /**
     * Retrieves the Module Panel Css Class
     * @return string
     */
    protected function getBlockHeadCssClass()
    {
        return "TotalPayCheckout";
    }
}
