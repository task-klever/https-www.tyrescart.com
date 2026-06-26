define([
    'jquery',
    'mage/url',
    'mage/storage',
    'Magento_Customer/js/customer-data'
], function ($, urlBuilder, storage, customerData) {
    'use strict';

    return function () {
        $(document).on('change', '.cart-item-option', function () {
            var itemId = $(this).data('item-id');
            var optionId = $(this).data('option-id');
            var isChecked = $(this).is(':checked');

            storage.post(
                urlBuilder.build('customcart/update/option'),
                JSON.stringify({
                    item_id: itemId,
                    option_id: optionId,
                    checked: isChecked
                }),
                true
            ).done(function (response) {
                // Refresh cart customer-data section to update mini-cart / totals
                customerData.reload(['cart'], true);
                // Optionally reload to refresh totals and item renderers
                window.location.reload();
            }).fail(function () {
                // On fail, you may want to notify the user
                window.location.reload();
            });
        });
    };
});
