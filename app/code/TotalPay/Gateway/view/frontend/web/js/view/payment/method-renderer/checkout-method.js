define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'TotalPay_Gateway/js/action/lightbox',
        'TotalPay_Gateway/js/model/totalpay-service'
    ],
    function (
        $,
        ko,
        quote,
        urlBuilder,
        storage,
        customerData,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators,
        url,
        errorProcessor,
        fullScreenLoader,
        lightboxAction,
        totalPayService
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'TotalPay_Gateway/payment/method/checkout/form',
                iframeSrc: ''
            },
            isInAction: totalPayService.isInAction,
            isLightboxReady: totalPayService.isLightboxReady,
            iframeHeight: totalPayService.iframeHeight,
            iframeWidth: totalPayService.iframeWidth,
            initialize: function() {
                this._super();
                $(window).bind('message', function(event) {
                    // totalPayService.iframeResize(event.originalEvent.data);
                });
            },

            initObservable: function () {
                var self = this;
                this._super().observe([
                    'iframeSrc'
                ]);

                return this;
            },

            resetIframe: function() {
                this.isLightboxReady(false);
                this.isInAction(false);
            },

            isIframeEnabled: function () {
                return window.checkoutConfig.payment[this.item.method]['enable_iframe'] === '1';
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.stopPerformingPlaceOrderAction();
                    }).done(this.afterPlaceOrder.bind(this));

                    return true;
                }

                self.stopPerformingPlaceOrderAction();
                return false;
            },

            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },

            afterPlaceOrder: function (orderId, status) {
                var self = this;

                if (!self.isIframeEnabled()) {
                    window.location.replace(url.build('totalpay/checkout/index'));
                } else {
                    var serviceUrl = urlBuilder.createUrl(
                        '/orders/:order_id/iframe',
                        {
                            order_id: orderId
                        }
                    );
                    var payload = {};

                    storage.post(serviceUrl, JSON.stringify(payload)).fail(
                        function (response) {
                            // @TODO: handle fail case
                            errorProcessor.process(response, self.messageContainer);
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                        function (response) {
                            if (response) {
                                self.iframeSrc(response.redirect_payment_url);
                                totalPayService.isInAction(true);
                                totalPayService.isLightboxReady(true);
                                lightboxAction();
                                self.stopPerformingPlaceOrderAction();
                            } else {
                                // @TODO: handle fail case
                                errorProcessor.process(response, self.messageContainer);
                                fullScreenLoader.stopLoader();
                                self.isPlaceOrderActionAllowed(true);

                                // capture all click events
                                document.addEventListener('click', totalPayService.leaveIframeForLinks, true);
                            }
                        }
                    );
                }
            },

            /**
             * Hide loader when iframe is fully loaded.
             * @returns {void}
             */
            iframeLoaded: function() {
                var self = this;
                this.stopPerformingPlaceOrderAction();
            },

            /**
             * Start performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            startPerformingPlaceOrderAction: function () {
                this.isPlaceOrderActionAllowed(false);
                fullScreenLoader.startLoader();
            },

            /**
             * Stop performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            stopPerformingPlaceOrderAction: function () {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            },

        });
    }
);
