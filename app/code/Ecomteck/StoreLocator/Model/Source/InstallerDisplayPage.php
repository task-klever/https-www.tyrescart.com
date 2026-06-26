<?php

namespace Ecomteck\StoreLocator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class InstallerDisplayPage implements OptionSourceInterface
{
    const REFERENCE_CART = 'reference_cart';
    const SHOPPING_CART  = 'shopping_cart';

    public function toOptionArray()
    {
        return [
            ['value' => self::REFERENCE_CART, 'label' => __('Reference Cart')],
            ['value' => self::SHOPPING_CART,  'label' => __('Shopping Cart')],
        ];
    }
}
