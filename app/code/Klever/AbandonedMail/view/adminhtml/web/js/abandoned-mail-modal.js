/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
], function ($, confirm, $t) {
    'use strict';

    return {
        send: function (customerEmail, sendUrl, isRestricted) {
            let message;
            let confirmActions;

            if (isRestricted) {
                message = $t('Abandoned mail already sent. Please try again after some time.');
                confirmActions = {
                    confirm: function () {
                        return false;
                    },
                    cancel: function () {
                        return false;
                    },
                };
            } else {
                message = $t('Are you sure you want to send an abandonment reminder to %1?').replace('%1', customerEmail);
                confirmActions = {
                    confirm: function () {
                        const formKey = (window.FORM_KEY) || $('input[name="form_key"]').val() || '';
                        
                        $.ajax({
                            url: sendUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                form_key: formKey
                            },
                            showLoader: true,
                            success: function (response) {
                                if (response.success) {
                                    require(['Magento_Ui/js/modal/alert'], function (alert) {
                                        alert({
                                            title: $t('Success'),
                                            content: response.message,
                                        });
                                    });
                                    location.reload();
                                } else {
                                    if (response.restricted) {
                                        require(['Magento_Ui/js/modal/alert'], function (alert) {
                                            alert({
                                                title: $t('Abandoned Mail'),
                                                content: $t('Abandoned mail already sent. Please try again after some time.'),
                                            });
                                        });
                                    } else {
                                        require(['Magento_Ui/js/modal/alert'], function (alert) {
                                            alert({
                                                title: $t('Error'),
                                                content: response.message,
                                            });
                                        });
                                    }
                                }
                            },
                            error: function () {
                                require(['Magento_Ui/js/modal/alert'], function (alert) {
                                    alert({
                                        title: $t('Error'),
                                        content: $t('An error occurred while sending the abandoned mail.'),
                                    });
                                });
                            },
                        });
                    },
                    cancel: function () {
                        return false;
                    },
                };
            }

            confirm({
                title: $t('Confirm Abandoned Mail'),
                content: message,
                modalClass: 'abandoned-mail-confirm-modal',
                actions: confirmActions,
            });
        },
    };
});

