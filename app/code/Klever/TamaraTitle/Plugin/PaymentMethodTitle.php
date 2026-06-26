<?php

namespace Klever\TamaraTitle\Plugin;

use Magento\Payment\Model\Method\Adapter;

class PaymentMethodTitle
{
    private const CUSTOM_TITLES = [
        'tamara_pay_by_instalments'   => 'Tamara - Pay in installments',
        'tamara_pay_by_instalments_2' => 'Tamara - Pay in 2 installments',
        'tamara_pay_by_instalments_3' => 'Tamara - Pay in 3 installments',
        'tamara_pay_by_instalments_4' => 'Tamara - Pay in 4 installments',
        'tamara_pay_by_instalments_5' => 'Tamara - Pay in 5 installments',
        'tamara_pay_by_instalments_6' => 'Tamara - Pay in 6 installments',
        'tamara_pay_now'              => 'Tamara - Pay now',
        'tamara_pay_later'            => 'Tamara - Pay later',
        'tamara_pay_next_month'       => 'Tamara - Pay next month',
    ];

    public function afterGetTitle(Adapter $subject, $result)
    {
        $code = $subject->getCode();

        if (isset(self::CUSTOM_TITLES[$code])) {
            return self::CUSTOM_TITLES[$code];
        }

        return $result;
    }
}
