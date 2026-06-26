/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery','Magento_Ui/js/form/element/abstract','Ecomteck_StoreLocator/js/async'],function($, Abstract, async) {
    return Abstract.extend({
        defaults: {
        },
        autocomplete: [],

        streetFieldSelector: "input[name='address']",

        fields: {
            postal_code: { short_name: "input[name='postcode']" },
            locality:    { long_name:  "input[name='city']" },
            postal_town: { short_name: "input[name='city']" },
            country:     { short_name: "select[name='country']" },
            administrative_area_level_1: {long_name: "input[name='region']:visible"}
        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            var self = this;
            async.load(
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyC7IjE7Eni549eBPtowO7ATA56syACPcoE&libraries=places&callback=initAutocomplete',
                requirejs,
                function () {
                    require(['Ecomteck_StoreLocator/js/vendor/jquery.geocomplete.min'],function(){
                        
                    })
                }.bind(this),
                {isBuild: false}
            );
            return this._super();
        },


        /**
         * Init observables
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'items'
                ]);
        },

        renderAutoComplete: function(target, viewModel){
            var self = this;
            require(['Ecomteck_StoreLocator/js/vendor/jquery.geocomplete.min'],function(){
                var latElement = $(target).closest('fieldset').find("input[name='latitude']");
                var lngElement = $(target).closest('fieldset').find("input[name='longitude']");
                var options = {
                    map: ".map_canvas",
                    types: ["geocode", "establishment"],
                    markerOptions: {
                        draggable: true
                    },
                }
                if(lngElement.length && latElement.length){
                    options.location =  [latElement.val(),lngElement.val()];
                }

                $(target).geocomplete(options).bind("geocode:result", function(event, result){
                    console.log(result);
                    self.fillAddressFields(target,result);
                }).bind("geocode:dragged", function(event, result){
                    self.fillLangLat(target,result);
                });
            })
        },

        findComponentValue: function (place, type, subtype) {
            for (var i = 0; i < place.address_components.length; i++) {
                for (var j = 0; j < place.address_components[i].types.length; j++) {
                    var addressType = place.address_components[i].types[j];
                    if (addressType === type) {
                        return place.address_components[i][subtype];
                    }
                }
            }
            return null;
        },

        shouldSplitStreetFields: function () {
            return true;
        },

        fillStreetFields: function (element, place) {
            var streetNumberElement = $(element).closest('fieldset').find("input[name='address']");
            streetNumberElement.val(place.name).change();
        },

        fillOtherFields: function (element, place) {
            for (var type in this.fields) {
                if (this.fields.hasOwnProperty(type)) {
                    for (var subtype in this.fields[type]) {
                        if (this.fields[type].hasOwnProperty(subtype)) {
                            var selector = this.fields[type][subtype];
                            var form = $(element).closest('fieldset');
                            var field = form.find(selector);
                            var value = this.findComponentValue(place, type, subtype);

                            if (value !== null) {
                                if (field.length) {
                                    field.val(value).change();
                                } 
                                if (type === 'administrative_area_level_1') {
                                    // Couldn't find visible region input, dealing with a dropdown.
                                    var regionSelector = "select[name='address[region_id]'] option";
                                    form.find(regionSelector).filter(function () {
                                        return $(this).text() === value;
                                    }).prop('selected', true).change();
                                }
                            }
                        }
                    }
                }
            }
        },

        fillLangLat: function (element, loc) {
            if(loc){
                console.log(loc.toJSON());
                var l = loc.toJSON();
                var latElement = $(element).closest('fieldset').find("input[name='latitude']");
                latElement.val(l.lat).change();
                var lngElement = $(element).closest('fieldset').find("input[name='longitude']");
                lngElement.val(l.lng).change();
            }
        },

        fillAddressFields: function (element,place) {
            if (typeof place === 'undefined') {
                return;
            }
            this.fillStreetFields(element, place);
            this.fillOtherFields(element, place);
            this.fillLangLat(element, place.geometry.location);
        }
    });
});