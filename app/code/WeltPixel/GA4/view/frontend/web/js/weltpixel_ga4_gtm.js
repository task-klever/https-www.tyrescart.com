define([
    'jquery',
    'mage/url'
    ], function ($,urlBuilder) {
    "use strict";

    return {
        trackPromotion: function(options) {
            $(document).ready(function() {

                var wpPersDl = options.persDataLayer;
                urlBuilder.setBaseUrl(window.BASE_URL);

                var dataLayerPromotionViewPush  = options.enabled && !(options.disableDatalayerEvents && options.serverSidePromotionViewEnabled);
                var dataLayerPromotionClickPush  = options.enabled && !(options.disableDatalayerEvents && options.serverSidePromotionClickEnabled);

                var promotionViewUrl = urlBuilder.build("weltpixel_ga4/track/promotionview/");
                var promotionClickUrl = urlBuilder.build("weltpixel_ga4/track/promotionclick/");

                /**  Track the promotion clicks   */
                if (dataLayerPromotionClickPush || options.serverSidePromotionClickEnabled) {
                    $('[data-track-promo-id]').click(function () {
                        var promoId = $(this).attr('data-track-promo-id'),
                            promoName = $(this).attr('data-track-promo-name'),
                            promoCreative = $(this).attr('data-track-promo-creative'),
                            promoPositionSlot = $(this).attr('data-track-promo-position'),
                            promoItemIds = $(this).attr('data-unqiue-id-items'),
                            promoProductIds = $(this).attr('data-track-promo-product-ids'),
                            promoItems = $.parseJSON($("#" + promoItemIds).html() || '[]');

                        var promoObj = {
                            'promotion_id': promoId,
                            'promotion_name': promoName,
                            'creative_name': promoCreative,
                            'creative_slot': promoPositionSlot,
                            'items': promoItems
                        };

                        if (window.ga4AllowServices && dataLayerPromotionClickPush && promoItemIds) {
                            window.dataLayer.push({ecommerce: null});
                            window.dataLayer.push({
                                'event': 'select_promotion',
                                'ecommerce': {
                                    'promotion_id': promoId,
                                    'promotion_name': promoName,
                                    'creative_name': promoCreative,
                                    'creative_slot': promoPositionSlot,
                                    'items': promoItems
                                }
                            });
                            wpPersDl.setPromotionClick(promoObj);
                        }

                        $.post({
                            url: promotionClickUrl,
                            global: false,
                            data: {
                                'promotion_id': promoId,
                                'promotion_name': promoName,
                                'creative_name': promoCreative,
                                'creative_slot': promoPositionSlot,
                                'product_ids': promoProductIds,
                                'promo_items_uniqueid': promoItemIds,
                                'dataLayerPush': dataLayerPromotionClickPush
                            }
                        }, function (pushData) {
                            if (window.ga4AllowServices && dataLayerPromotionClickPush && pushData) {
                                for (let index in pushData) {
                                    window.dataLayer.push({ecommerce: null});
                                    window.dataLayer.push(pushData[index]);
                                }
                            }
                        });
                    });
                }

                /** Track the promotion views */
                if (dataLayerPromotionViewPush || options.serverSidePromotionViewEnabled) {
                    var promotionViews = [];

                    $('[data-track-promo-id]').each(function() {
                        var promoId = $(this).attr('data-track-promo-id'),
                            promoName = $(this).attr('data-track-promo-name'),
                            promoCreative = $(this).attr('data-track-promo-creative'),
                            promoPositionSlot = $(this).attr('data-track-promo-position'),
                            promoItemIds = $(this).attr('data-unqiue-id-items'),
                            promoProductIds = $(this).attr('data-track-promo-product-ids'),
                            promoItems = $.parseJSON($("#" + promoItemIds).html() || '[]');

                        if (window.ga4AllowServices && dataLayerPromotionViewPush && promoItemIds) {
                            window.dataLayer.push({ecommerce: null});
                            window.dataLayer.push({
                                'event': 'view_promotion',
                                'ecommerce': {
                                    'promotion_id': promoId,
                                    'promotion_name': promoName,
                                    'creative_name': promoCreative,
                                    'creative_slot': promoPositionSlot,
                                    'items': promoItems
                                }
                            });
                        }

                        promotionViews.push({
                            'promotion_id': promoId,
                            'promotion_name': promoName,
                            'creative_name': promoCreative,
                            'creative_slot': promoPositionSlot,
                            'product_ids': promoProductIds,
                            'promo_items_uniqueid': promoItemIds
                        });
                    });

                    $.post({
                        url: promotionViewUrl,
                        global: false,
                        data: {
                            'promotion_views': promotionViews,
                            'dataLayerPush': dataLayerPromotionViewPush
                        }
                    }, function (pushData) {
                        if (window.ga4AllowServices && dataLayerPromotionViewPush && pushData) {
                            for (let index in pushData) {
                                window.dataLayer.push({ecommerce: null});
                                window.dataLayer.push(pushData[index]);
                            }
                        }
                    });
                }
            });
        }
    };

});
