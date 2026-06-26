window.initInstallerFormLogic = function ($el) {
    const installerTypeSelect = document.getElementById('installer-type');
    const dateSelect = document.getElementById('cart-installer-date-select');
    const timeSelect = document.getElementById('cart-installer-time-select');

    const installerLocations = document.getElementById('installer-locations');
    const installerLocationsHeading = document.querySelector('.installer-locations-heading');
    const dateSelectHeading = document.querySelector('.cart-installer-date-select-heading');
    const timeSelectHeading = document.querySelector('.cart-installer-time-select-heading');

    const hoursDataElem = document.getElementById('store-opening-hours-data');
    const openingHoursJson = hoursDataElem.getAttribute('data-opening-hours');
    const allStoreOpeningHours = JSON.parse(openingHoursJson);

    const skipDaysJson = hoursDataElem.getAttribute('data-skip-days');
    const storeSkipDays = JSON.parse(skipDaysJson || '{}');

    const cutoffTimeJson = hoursDataElem.getAttribute('data-cutoff-time');
    const storeCutoffTime = JSON.parse(cutoffTimeJson || '{}');

    const skipHoursJson = hoursDataElem.getAttribute('data-skip-hours');
    const storeSkipHours = JSON.parse(skipHoursJson || '{}');

    const storeTimezone = hoursDataElem.getAttribute('data-store-timezone') || 'Asia/Dubai';

    // Detect if we are on the reference cart page
    const isRefCartPage = window.location.pathname.indexOf('storelocator/refcart') !== -1 || window.location.search.indexOf('ref=cart') !== -1;

    // Safely set cart-page elements (they don't exist on ref cart page)
    function setCartPageDefaults() {
        var countrySelect = document.querySelector('select[name="country_id"]');
        if (countrySelect) {
            countrySelect.value = "AE";
            countrySelect.dispatchEvent(new Event('change'));
        }
        var shippingMethod = document.querySelector('#shipping_method_storepickup');
        if (shippingMethod) {
            shippingMethod.checked = true;
            shippingMethod.dispatchEvent(new Event('change'));
        }
    }

    // Safely show/hide loader
    function showLoader() {
        var loader = document.getElementById('klever-loader');
        if (loader) loader.classList.remove('hidden');
    }
    function hideLoader() {
        var loader = document.getElementById('klever-loader');
        if (loader) loader.classList.add('hidden');
    }

    if (!installerTypeSelect) {
        console.warn('Installer type select not found');
        return;
    }

    /**
     * Get current date/time in the store's timezone
     */
    function getNowInStoreTimezone() {
        const nowStr = new Date().toLocaleString('en-US', { timeZone: storeTimezone });
        return new Date(nowStr);
    }

    /**
     * Convert 12-hour time string (e.g. "10:00 AM") to minutes since midnight
     */
    function timeToMinutes(timeStr) {
        const [time, modifier] = timeStr.trim().split(' ');
        let [hours, minutes] = time.split(':').map(Number);
        if (modifier === 'PM' && hours !== 12) hours += 12;
        if (modifier === 'AM' && hours === 12) hours = 0;
        return hours * 60 + minutes;
    }

    /**
     * Convert "HH:MM" (24h) string to minutes since midnight
     */
    function time24ToMinutes(timeStr) {
        if (!timeStr) return 0;
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    /**
     * Get time slots for a given store and date
     * Applies cutoff time and skip hours filtering for today
     */
    function getTimeSlots(storeId, selectedDate) {
        var weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        var dateObj = new Date(selectedDate);
        var dayName = weekdays[dateObj.getDay()];

        if (!allStoreOpeningHours[storeId] || !allStoreOpeningHours[storeId][dayName]) {
            return [];
        }

        var slots = allStoreOpeningHours[storeId][dayName];
        if (!slots || slots.length === 0) {
            return [];
        }

        var skipDays = storeSkipDays[storeId] || 0;
        var now = getNowInStoreTimezone();
        var todayStr = (now.getMonth() + 1) + '/' + now.getDate() + '/' + now.getFullYear();

        var isToday = (selectedDate === todayStr);

        // Only apply cutoff/skip_hours filtering if skip_days === 0 AND date is today
        if (skipDays === 0 && isToday) {
            var currentMinutes = now.getHours() * 60 + now.getMinutes();

            // 1. Cutoff Time check: if current time >= cutoff, return empty (today blocked)
            var cutoff = storeCutoffTime[storeId] || '';
            if (cutoff) {
                var cutoffMinutes = time24ToMinutes(cutoff);
                if (currentMinutes >= cutoffMinutes) {
                    return [];
                }
            }

            // 2. Skip Hours filter: only include slots starting after (now + skipHours)
            var skipHrs = storeSkipHours[storeId] || 0;
            var earliestSlotMinutes = currentMinutes + (skipHrs * 60);

            var filteredSlots = [];
            slots.forEach(function (timeSlot) {
                var parts = timeSlot.split('-');
                var startTimeStr = parts[0].trim();
                var slotStartMinutes = timeToMinutes(startTimeStr);

                if (slotStartMinutes >= earliestSlotMinutes) {
                    filteredSlots.push(timeSlot);
                }
            });

            return filteredSlots;
        }

        // Future dates: return all slots
        return slots.slice();
    }

    /**
     * Generate available dates for a store
     * Applies skip_days, cutoff_time, and skip_hours logic
     * Returns up to 15 available dates
     */
    function generateDates(storeId) {
        var options = ['<option value="">Select a Date</option>'];
        var now = getNowInStoreTimezone();

        var skipDays = storeId ? (storeSkipDays[storeId] || 0) : 0;

        // Start date = today + skip_days
        var startDate = new Date(now);
        startDate.setHours(0, 0, 0, 0);
        startDate.setDate(startDate.getDate() + skipDays);

        var maxLookahead = 45; // look ahead up to 45 days
        var maxDates = 15;     // collect up to 15 available dates
        var collected = 0;

        for (var i = 0; i < maxLookahead && collected < maxDates; i++) {
            var date = new Date(startDate);
            date.setDate(startDate.getDate() + i);

            var formattedValue = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();

            // Check if this date has available time slots
            var slots = getTimeSlots(storeId, formattedValue);

            if (slots.length > 0) {
                var monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
                var monthText = monthNames[date.getMonth()];
                var displayText = date.getDate().toString().padStart(2, '0') + '-' + monthText + '-' + date.getFullYear();

                options.push('<option value="' + formattedValue + '">' + displayText + '</option>');
                collected++;
            }
        }

        if (options.length === 1) {
            options.push('<option value="" disabled>No available dates</option>');
        }

        return options.join('');
    }

    // ---- Event Listeners ----

    installerTypeSelect.addEventListener('change', function () {
        if (dateSelect) dateSelect.value = '';
        if (timeSelect) {
            timeSelect.value = '';
            timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
        }

        const selectedOption = installerTypeSelect.options[installerTypeSelect.selectedIndex];
        const installerType = selectedOption.getAttribute('data-installer-type');

        var pickupLocationField = document.getElementById('pickup-location-field');
        var pickupLocationInput = document.getElementById('cart-pickup-location');
        var pickupLocationError = document.getElementById('pickup-location-error');

        if (installerType == 'without_fitting') {
            toggleDisplay(installerLocations, true);
            toggleDisplay(installerLocationsHeading, true);
            toggleDisplay(dateSelect, true);
            toggleDisplay(dateSelectHeading, true);
            toggleDisplay(timeSelect, true);
            toggleDisplay(timeSelectHeading, true);
            if (pickupLocationField) pickupLocationField.style.display = 'none';

        } else {
            toggleDisplay(installerLocations, false);
            toggleDisplay(installerLocationsHeading, false);
            toggleDisplay(dateSelect, false);
            toggleDisplay(dateSelectHeading, false);
            toggleDisplay(timeSelect, false);
            toggleDisplay(timeSelectHeading, false);

            // Show pickup location only for mobile van
            if (pickupLocationField) {
                pickupLocationField.style.display = (installerType == 'mobile_van') ? '' : 'none';
            }
        }

        // Reset pickup location
        if (pickupLocationInput) pickupLocationInput.value = '';
        if (pickupLocationError) pickupLocationError.classList.add('hidden');

        showLoader();
        fetch(BASE_URL + 'storelocator/ajax/getstores', {
            headers: {
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                installer_type: installerType,
                uenc: btoa(window.location.href)
            }),
            method: "POST",
            mode: "cors",
            credentials: "include",
        })
            .then(response => response.json())
            .then(data => {
                if (installerType != 'without_fitting') {
                    document.getElementById('installer-locations').innerHTML = data.html;

                    // Update skip_days, cutoff_time, skip_hours if returned from AJAX
                    if (data.store_skip_days) {
                        Object.assign(storeSkipDays, data.store_skip_days);
                    }
                    if (data.store_cutoff_time) {
                        Object.assign(storeCutoffTime, data.store_cutoff_time);
                    }
                    if (data.store_skip_hours) {
                        Object.assign(storeSkipHours, data.store_skip_hours);
                    }
                    if (data.store_google_maps && typeof storeGoogleMaps !== 'undefined') {
                        Object.assign(storeGoogleMaps, data.store_google_maps);
                    }
                    if (data.store_cities && typeof storeCities !== 'undefined') {
                        Object.assign(storeCities, data.store_cities);
                    }
                }
                setCartPageDefaults();
                hideLoader();

                // On ref cart page, "without_fitting" completes immediately — show checkout button
                if (isRefCartPage && installerType == 'without_fitting') {
                    var proceedBtn = document.getElementById('refcart-proceed-checkout');
                    if (proceedBtn) {
                        proceedBtn.classList.remove('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoader();
            });
    });

    installerLocations.addEventListener('change', function () {
        dateSelect.value = '';
        timeSelect.value = '';
        timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());

        const selectedOption = installerLocations.options[installerLocations.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const storeId = selectedOption.value;
            dateSelect.innerHTML = generateDates(storeId);
        } else {
            dateSelect.innerHTML = '<option value="">Select a Date</option>';
        }

        // Show/hide "See On Map" link
        var seeOnMapLink = document.getElementById('see-on-map-link');
        if (seeOnMapLink) {
            var selectedVal = installerLocations.value;
            if (selectedVal && typeof storeGoogleMaps !== 'undefined' && storeGoogleMaps[selectedVal]) {
                seeOnMapLink.classList.remove('hidden');
            } else {
                seeOnMapLink.classList.add('hidden');
            }
        }

        var installerId = installerLocations.value;
        var installerTypeValue = installerTypeSelect.value;
        fetch(BASE_URL + 'storelocator/ajax/setinstaller', {
            headers: {
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                pickup_type: installerTypeValue,
                pickup_store: installerId,
                uenc: btoa(window.location.href)
            }),
            method: "POST",
            mode: "cors",
            credentials: "include",
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            setCartPageDefaults();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoader();
        });
    });

    dateSelect.addEventListener('change', function () {
        var selectedDate = this.value;
        if (!selectedDate) {
            timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
            return;
        }

        var selectedInstallerOption = document.querySelector('#installer-locations option:checked');
        if (!selectedInstallerOption || !selectedInstallerOption.value) {
            console.warn('No installer selected');
            return;
        }

        var installerId = selectedInstallerOption.getAttribute('data-store-id');

        // Use getTimeSlots which handles all filtering
        var availableSlots = getTimeSlots(installerId, selectedDate);

        timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());

        if (availableSlots.length > 0) {
            availableSlots.forEach(function (timeSlot) {
                var option = document.createElement('option');
                option.value = timeSlot;
                option.textContent = timeSlot;
                timeSelect.appendChild(option);
            });

            // Remove existing event listener to avoid duplicates
            timeSelect.removeEventListener('change', timeSelectChangeHandler);
            // Add new event listener
            timeSelect.addEventListener('change', timeSelectChangeHandler);
        }
    });

    // Time select change handler function
    function timeSelectChangeHandler() {
        var selectedTime = timeSelect.value;
        if (selectedTime !== '') {
            var installerTypeValue = installerTypeSelect.value;
            var installerLocationValue = installerLocations.value;
            var installerDateValue = dateSelect.value;
            var installerTimeValue = timeSelect.value;

            if (installerTypeValue && installerLocationValue && installerDateValue && installerTimeValue) {
                var installerId = installerLocationValue;
                showLoader();
                fetch(BASE_URL + 'storelocator/ajax/setinstaller', {
                    headers: {
                        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                    },
                    body: new URLSearchParams(function() {
                        var params = {
                            form_key: hyva.getFormKey(),
                            pickup_type: installerTypeValue,
                            pickup_store: installerId,
                            pickup_date: installerDateValue,
                            pickup_time: installerTimeValue,
                            uenc: btoa(window.location.href)
                        };
                        var pickupLocInput = document.getElementById('cart-pickup-location');
                        if (pickupLocInput && pickupLocInput.value) {
                            params.pickup_location = pickupLocInput.value;
                        }
                        return params;
                    }()),
                    method: "POST",
                    mode: "cors",
                    credentials: "include",
                })
                    .then(response => response.json())
                    .then(data => {
                        hideLoader();
                        setCartPageDefaults();

                        // On reference cart page, show proceed to checkout button
                        if (isRefCartPage) {
                            var proceedBtn = document.getElementById('refcart-proceed-checkout');
                            if (proceedBtn) {
                                proceedBtn.classList.remove('hidden');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        hideLoader();
                    });
            } else {
                console.log('Some values are missing.');
            }
        }
    }

    // Initialize with empty date options
    dateSelect.innerHTML = '<option value="">Select a Date</option>';


    function toggleDisplay(el, hide) {
        if (el) {
            el.style.display = hide ? 'none' : '';
        }
    }

    // --- Pickup Location Autocomplete + City-based Geofencing for Mobile Van ---
    var cartCityBoundsCache = {};
    var cartPickupAutocomplete = null;

    function getCartCityBounds(cityName, callback) {
        if (cartCityBoundsCache[cityName]) {
            callback(cartCityBoundsCache[cityName]);
            return;
        }
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: cityName + ', UAE' }, function(results, status) {
            if (status === 'OK' && results[0] && results[0].geometry) {
                var bounds = results[0].geometry.viewport || results[0].geometry.bounds;
                cartCityBoundsCache[cityName] = bounds;
                callback(bounds);
            } else {
                callback(null);
            }
        });
    }

    var pickupLocationInput = document.getElementById('cart-pickup-location');
    if (pickupLocationInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
        initPickupLocationAutocomplete();
    } else if (pickupLocationInput) {
        var _checkGoogleInterval = setInterval(function() {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                clearInterval(_checkGoogleInterval);
                initPickupLocationAutocomplete();
            }
        }, 500);
    }

    function initPickupLocationAutocomplete() {
        var input = document.getElementById('cart-pickup-location');
        if (!input || input._autocompleteInit) return;
        input._autocompleteInit = true;

        cartPickupAutocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'ae' }
        });

        cartPickupAutocomplete.addListener('place_changed', function() {
            var errorEl = document.getElementById('pickup-location-error');
            if (errorEl) errorEl.classList.add('hidden');
        });
    }

    // Update autocomplete bounds when installer location changes (for mobile van)
    function updateCartPickupBounds() {
        if (!cartPickupAutocomplete) return;
        var selectedVal = installerLocations.value;
        var storeCity = (typeof storeCities !== 'undefined' && storeCities[selectedVal]) ? storeCities[selectedVal] : '';
        var input = document.getElementById('cart-pickup-location');
        if (input) input.value = '';

        if (storeCity) {
            getCartCityBounds(storeCity, function(bounds) {
                if (bounds) {
                    cartPickupAutocomplete.setBounds(bounds);
                    cartPickupAutocomplete.setOptions({ strictBounds: true });
                }
            });
        } else {
            cartPickupAutocomplete.setOptions({ strictBounds: false });
            cartPickupAutocomplete.setBounds(null);
        }
    }

    // Hook into installer location change to update bounds
    installerLocations.addEventListener('change', function() {
        var selectedOption = installerTypeSelect.options[installerTypeSelect.selectedIndex];
        var installerType = selectedOption ? selectedOption.getAttribute('data-installer-type') : '';
        if (installerType === 'mobile_van') {
            updateCartPickupBounds();
        }
    });
};
