
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'totalpay_checkout',
                component: 'TotalPay_Gateway/js/view/payment/method-renderer/checkout-method'
            }//,
            // {
            //     type: 'TotalPay_direct',
            //     component: 'TotalPay_Gateway/js/view/payment/method-renderer/direct-method'
            // }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
