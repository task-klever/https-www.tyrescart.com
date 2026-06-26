define(
    [
        'jquery',
        'TotalPay_Gateway/js/action/restore-cart',
        'TotalPay_Gateway/js/model/totalpay-service',
        'Magento_Ui/js/modal/modal',
        'mage/translate'
    ],
    function($, restoreCartAction, totalPayService, modal, $t) {
        'use strict';

        return function() {

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                clickableOverlay: false,
                modalClass: 'totalpay-lightbox',
                buttons: [],
                keyEventHandlers: {

                    /**
                     * Tab key press handler,
                     * set focus to elements
                     */
                    tabKey: function () {
                        if (document.activeElement === this.modal[0]) {
                            this._setFocus('start');
                        }
                    },

                    /**
                     * Escape key press handler,
                     * close modal window
                     * @param {Object} event - event
                     */
                    escapeKey: function (event) {
                        //do nothing to avoid close will remove later

                        this.closeModal(event);
                    }
                },
                closed: function() {
                    // restoreCartAction();
                    totalPayService.isInAction(false);
                    totalPayService.isLightboxReady(false);
                },

                opened: function(event) {
                    $(".totalpay-lightbox .action-close").hide();
                }
            };
            $("#totalpay-iframe-container").modal(options).modal('openModal');
        };
    }
);
