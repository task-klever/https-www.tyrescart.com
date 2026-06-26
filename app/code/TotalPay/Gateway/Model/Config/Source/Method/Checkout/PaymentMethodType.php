<?php


namespace TotalPay\Gateway\Model\Config\Source\Method\Checkout;

use \TotalPay\Gateway\Helper\Data as DataHelper;

/**
 * Checkout Payment Method Types Model Source
 * Class PaymentMethodType
 * @package TotalPay\Gateway\Model\Config\Source\Method\Checkout
 */
class PaymentMethodType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Builds the options for the MultiSelect control in the Admin Zone
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => DataHelper::CREDIT_CARD,
                'label' => __('Card'),
            ]
        ];
    }
}
