document.addEventListener('DOMContentLoaded', function () {

    var hoursDataElem = document.getElementById('store-opening-hours-data');
    if (!hoursDataElem) return;

    var allStoreOpeningHours = JSON.parse(hoursDataElem.getAttribute('data-opening-hours') || '{}');
    var storeSkipDays = JSON.parse(hoursDataElem.getAttribute('data-skip-days') || '{}');
    var storeCutoffTime = JSON.parse(hoursDataElem.getAttribute('data-cutoff-time') || '{}');
    var storeSkipHours = JSON.parse(hoursDataElem.getAttribute('data-skip-hours') || '{}');
    var storeTimezone = hoursDataElem.getAttribute('data-store-timezone') || 'Asia/Dubai';
    var nofitmentId = hoursDataElem.getAttribute('data-nofitment-id') || '';
    var labelSelectDate = hoursDataElem.getAttribute('data-label-select-date') || 'Select a Date';
    var labelSelectTime = hoursDataElem.getAttribute('data-label-select-time') || 'Select Time Slot';

    // ---- Timezone & time helpers (same as installer.js) ----

    function getNowInStoreTimezone() {
        var nowStr = new Date().toLocaleString('en-US', { timeZone: storeTimezone });
        return new Date(nowStr);
    }

    function timeToMinutes(timeStr) {
        var parts = timeStr.trim().split(' ');
        var time = parts[0];
        var modifier = parts[1];
        var timeParts = time.split(':');
        var hours = parseInt(timeParts[0], 10);
        var minutes = parseInt(timeParts[1], 10);
        if (modifier === 'PM' && hours !== 12) hours += 12;
        if (modifier === 'AM' && hours === 12) hours = 0;
        return hours * 60 + minutes;
    }

    function time24ToMinutes(timeStr) {
        if (!timeStr) return 0;
        var parts = timeStr.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    // ---- Slot/Date generation (same logic as installer.js) ----

    function getTimeSlots(storeId, selectedDate) {
        var weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        var dateObj = new Date(selectedDate);
        var dayName = weekdays[dateObj.getDay()];

        if (!allStoreOpeningHours[storeId] || !allStoreOpeningHours[storeId][dayName]) {
            return [];
        }

        var slots = allStoreOpeningHours[storeId][dayName];
        if (!slots || slots.length === 0) return [];

        var skipDays = storeSkipDays[storeId] || 0;
        var now = getNowInStoreTimezone();
        var todayStr = (now.getMonth() + 1) + '/' + now.getDate() + '/' + now.getFullYear();
        var isToday = (selectedDate === todayStr);

        if (skipDays === 0 && isToday) {
            var currentMinutes = now.getHours() * 60 + now.getMinutes();

            var cutoff = storeCutoffTime[storeId] || '';
            if (cutoff) {
                var cutoffMinutes = time24ToMinutes(cutoff);
                if (currentMinutes >= cutoffMinutes) return [];
            }

            var skipHrs = storeSkipHours[storeId] || 0;
            var earliestSlotMinutes = currentMinutes + (skipHrs * 60);

            var filteredSlots = [];
            slots.forEach(function (timeSlot) {
                var startTimeStr = timeSlot.split('-')[0].trim();
                var slotStartMinutes = timeToMinutes(startTimeStr);
                if (slotStartMinutes >= earliestSlotMinutes) {
                    filteredSlots.push(timeSlot);
                }
            });
            return filteredSlots;
        }

        return slots.slice();
    }

    function generateDateOptions(storeId) {
        var options = '<option value="">' + labelSelectDate + '</option>';
        var now = getNowInStoreTimezone();
        var skipDays = storeSkipDays[storeId] || 0;

        var startDate = new Date(now);
        startDate.setHours(0, 0, 0, 0);
        startDate.setDate(startDate.getDate() + skipDays);

        var maxLookahead = 45;
        var maxDates = 15;
        var collected = 0;

        for (var i = 0; i < maxLookahead && collected < maxDates; i++) {
            var date = new Date(startDate);
            date.setDate(startDate.getDate() + i);

            var formattedValue = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
            var slots = getTimeSlots(storeId, formattedValue);

            if (slots.length > 0) {
                var weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                var weekdaysAr = ["\u0627\u0644\u0623\u062D\u062F","\u0627\u0644\u0627\u062B\u0646\u064A\u0646","\u0627\u0644\u062B\u0644\u0627\u062B\u0627\u0621","\u0627\u0644\u0623\u0631\u0628\u0639\u0627\u0621","\u0627\u0644\u062E\u0645\u064A\u0633","\u0627\u0644\u062C\u0645\u0639\u0629","\u0627\u0644\u0633\u0628\u062A"];
                var monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
                var isArabic = document.documentElement.lang === 'ar' || document.documentElement.getAttribute('dir') === 'rtl';
                var displayText = isArabic
                    ? '(' + weekdaysAr[date.getDay()] + ') \u200E' + date.getDate().toString().padStart(2, '0') + '-' + monthNames[date.getMonth()] + '-' + date.getFullYear()
                    : date.getDate().toString().padStart(2, '0') + '-' + monthNames[date.getMonth()] + '-' + date.getFullYear() + ' (' + weekdays[date.getDay()] + ')';
                options += '<option value="' + formattedValue + '">' + displayText + '</option>';
                collected++;
            }
        }

        if (collected === 0) {
            options += '<option value="" disabled>No available dates</option>';
        }

        return options;
    }

    // ---- Safe loader ----

    function showLoader() {
        var loader = document.getElementById('klever-loader');
        if (loader) loader.classList.remove('hidden');
    }
    function hideLoader() {
        var loader = document.getElementById('klever-loader');
        if (loader) loader.classList.add('hidden');
    }

    // ---- AJAX helper ----

    function ajaxSetInstaller(params) {
        params.form_key = typeof hyva !== 'undefined' ? hyva.getFormKey() : '';
        params.uenc = btoa(window.location.href);
        return fetch(BASE_URL + 'storelocator/ajax/setinstaller', {
            headers: { contentType: "application/x-www-form-urlencoded; charset=UTF-8" },
            body: new URLSearchParams(params),
            method: "POST",
            mode: "cors",
            credentials: "include"
        }).then(function (r) { return r.json(); });
    }

    // ---- Pickup Location Autocomplete + City-based Geofencing for Mobile Van ----

    var cityBoundsCache = {};

    function getCityBounds(cityName, callback) {
        if (cityBoundsCache[cityName]) {
            callback(cityBoundsCache[cityName]);
            return;
        }
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: cityName + ', UAE' }, function(results, status) {
            if (status === 'OK' && results[0] && results[0].geometry) {
                var bounds = results[0].geometry.viewport || results[0].geometry.bounds;
                cityBoundsCache[cityName] = bounds;
                callback(bounds);
            } else {
                callback(null);
            }
        });
    }

    function initRefcartPickupAutocomplete() {
        document.querySelectorAll('.refcart-pickup-location').forEach(function(input) {
            if (input._autocompleteInit) return;
            // Skip mobile van booking card input — handled by Alpine with city-specific geofencing
            if (input.closest('.mobilevan-booking-card')) return;
            input._autocompleteInit = true;

            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: { country: 'ae' }
            });

            // Set bounds based on the store's city
            var storeCity = input.getAttribute('data-store-city') || '';
            if (storeCity) {
                getCityBounds(storeCity, function(bounds) {
                    if (bounds) {
                        autocomplete.setBounds(bounds);
                        autocomplete.setOptions({ strictBounds: true });
                    }
                });
            }

            // Store autocomplete instance on the input for later access
            input._autocomplete = autocomplete;

            autocomplete.addListener('place_changed', function() {
                var errorEl = input.closest('.refcart-selection').querySelector('.refcart-pickup-error');
                if (errorEl) errorEl.classList.add('hidden');
            });
        });
    }

    // Init autocomplete when Google Maps is ready
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
        initRefcartPickupAutocomplete();
    } else {
        var _checkGoogle = setInterval(function() {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                clearInterval(_checkGoogle);
                initRefcartPickupAutocomplete();
            }
        }, 500);
    }

    // ---- Store card expansion: populate dates ----

    // Entire store card is clickable — listen for clicks on the card itself
    document.addEventListener('click', function (e) {
        // Skip clicks on dropdowns and buttons inside the expanded section
        if (e.target.closest('.refcart-selection')) return;

        var card = e.target.closest('.refcart-store-card');
        if (!card) return;

        var storeId = card.getAttribute('data-store-id');
        if (!storeId) return;

        // Delay to let Alpine toggle activeStore and render x-show panel
        setTimeout(function () {
            var selection = card.querySelector('.refcart-selection[data-store-id="' + storeId + '"]');
            if (!selection || selection.offsetParent === null) return; // collapsed

            var dateSelect = selection.querySelector('.refcart-date-select');
            if (dateSelect) {
                dateSelect.innerHTML = generateDateOptions(storeId);
            }

            // Reset time and checkout
            var timeSelect = selection.querySelector('.refcart-time-select');
            if (timeSelect) {
                timeSelect.innerHTML = '<option value="">' + labelSelectTime + '</option>';
            }

            // AJAX set installer (store selected, no date/time yet)
            var activeMode = document.querySelector('[x-data]');
            var mode = 'normal';
            if (activeMode) {
                var match = activeMode.getAttribute('x-data').match(/activeMode:\s*'([^']+)'/);
                // Use Alpine data instead
            }
            // Determine mode from visible tab
            var modeBtn = document.querySelector('.refcart-page button.bg-theme-blue');
            if (modeBtn) {
                var text = modeBtn.textContent.trim();
                if (text.indexOf('Mobile Van') !== -1) mode = 'mobile_van';
                else if (text.indexOf('Without') !== -1) mode = 'without_fitting';
            }

            showLoader();
            ajaxSetInstaller({
                pickup_store: storeId,
                pickup_type: mode
            }).then(function () {
                hideLoader();
            }).catch(function () {
                hideLoader();
            });
        }, 350);
    });

    // ---- Date select change → populate time slots ----

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('refcart-date-select')) return;

        var dateSelect = e.target;
        var storeId = dateSelect.getAttribute('data-store-id');
        var selectedDate = dateSelect.value;

        var selection = dateSelect.closest('.refcart-selection');
        var timeSelect = selection.querySelector('.refcart-time-select');
        var checkoutBtn = selection.querySelector('.refcart-checkout-btn');

        // Reset
        timeSelect.innerHTML = '<option value="">' + labelSelectTime + '</option>';

        if (!selectedDate || !storeId) return;

        var slots = getTimeSlots(storeId, selectedDate);
        var isArabic = document.documentElement.lang === 'ar' || document.documentElement.getAttribute('dir') === 'rtl';
        slots.forEach(function (slot) {
            var opt = document.createElement('option');
            opt.value = slot;
            opt.textContent = isArabic ? '\u200E' + slot : slot;
            timeSelect.appendChild(opt);
        });
    });

    // ---- Time select change → save to quote + show checkout button ----

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('refcart-time-select')) return;

        var timeSelect = e.target;
        var storeId = timeSelect.getAttribute('data-store-id');
        var selectedTime = timeSelect.value;

        var selection = timeSelect.closest('.refcart-selection');
        var dateSelect = selection.querySelector('.refcart-date-select');
        var checkoutBtn = selection.querySelector('.refcart-checkout-btn');

        if (!selectedTime || !storeId) {
                return;
        }

        // Determine mode
        var mode = 'normal';
        var modeBtn = document.querySelector('.refcart-page button.bg-theme-blue');
        if (modeBtn) {
            var text = modeBtn.textContent.trim();
            if (text.indexOf('Mobile Van') !== -1) mode = 'mobile_van';
        }

        var ajaxParams = {
            pickup_store: storeId,
            pickup_type: mode,
            pickup_date: dateSelect.value,
            pickup_time: selectedTime
        };

        // Include pickup_location for mobile van
        if (mode === 'mobile_van') {
            var sel = timeSelect.closest('.refcart-selection');
            var pickupInput = sel ? sel.querySelector('.refcart-pickup-location') : null;
            if (pickupInput && pickupInput.value) {
                ajaxParams.pickup_location = pickupInput.value;
            }
        }

        showLoader();
        ajaxSetInstaller(ajaxParams).then(function () {
            hideLoader();
        }).catch(function () {
            hideLoader();
        });
    });

    // ---- Checkout button click → go to checkout ----

    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('refcart-checkout-btn')) return;
        window.location.href = BASE_URL + 'checkout?_=' + Date.now();
    });


    // ---- Without Fitment → save and redirect ----

    var withoutFittingBtn = document.getElementById('refcart-without-fitting-checkout');
    if (withoutFittingBtn) {
        withoutFittingBtn.addEventListener('click', function () {
            showLoader();
            fetch(BASE_URL + 'storelocator/ajax/getstores', {
                headers: { contentType: "application/x-www-form-urlencoded; charset=UTF-8" },
                body: new URLSearchParams({
                    form_key: typeof hyva !== 'undefined' ? hyva.getFormKey() : '',
                    installer_type: 'without_fitting',
                    uenc: btoa(window.location.href)
                }),
                method: "POST",
                mode: "cors",
                credentials: "include"
            })
            .then(function (r) { return r.json(); })
            .then(function () {
                hideLoader();
                window.location.href = BASE_URL + 'checkout?_=' + Date.now();
            })
            .catch(function () {
                hideLoader();
                window.location.href = BASE_URL + 'checkout?_=' + Date.now();
            });
        });
    }

});
