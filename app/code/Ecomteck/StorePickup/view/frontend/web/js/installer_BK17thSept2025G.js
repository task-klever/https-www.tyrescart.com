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

    if (!installerTypeSelect) {
        console.warn('Installer type select not found');
        return;
    }

    installerTypeSelect.addEventListener('change', function () {
        if (dateSelect) dateSelect.value = '';
        if (timeSelect) {
            timeSelect.value = '';
            timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
        }

        const selectedOption = installerTypeSelect.options[installerTypeSelect.selectedIndex];
        const installerType = selectedOption.getAttribute('data-installer-type');

        if (installerType == 'without_fitting') {
            toggleDisplay(installerLocations, true);
            toggleDisplay(installerLocationsHeading, true);
            toggleDisplay(dateSelect, true);
            toggleDisplay(dateSelectHeading, true);
            toggleDisplay(timeSelect, true);
            toggleDisplay(timeSelectHeading, true);
            
        }else{
            toggleDisplay(installerLocations, false);
            toggleDisplay(installerLocationsHeading, false);
            toggleDisplay(dateSelect, false);
            toggleDisplay(dateSelectHeading, false);
            toggleDisplay(timeSelect, false);
            toggleDisplay(timeSelectHeading, false);
        }

        document.getElementById('klever-loader').classList.remove('hidden');
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
            }
            document.getElementById('klever-loader').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('klever-loader').classList.add('hidden');
        });
    });

    installerLocations.addEventListener('change', function () {
        dateSelect.value = '';
        timeSelect.value = '';
        timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
    });

    dateSelect.addEventListener('change', function () {
        var weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        var selectedDate = this.value;
        var selectedDateObj = new Date(selectedDate);
        var selectedDay = weekdays[selectedDateObj.getDay()];

        var selectedInstallerOption = document.querySelector('#installer-locations option:checked');
        var installerId = selectedInstallerOption.getAttribute('data-store-id');
        var installerTimes = allStoreOpeningHours[installerId][selectedDay];

        timeSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());

        if (installerTimes) {
            installerTimes.forEach(function (timeSlots) {
                var storeStartTime = timeSlots.split('-');
                var timeStr = storeStartTime[0].trim();
                var endtimeStr = storeStartTime[1].trim();

                const convertTime = str => {
                    const [time, modifier] = str.split(' ');
                    let [hours, minutes] = time.split(':');
                    if (hours === '12') hours = '00';
                    if (modifier === 'PM') hours = parseInt(hours, 10) + 12;
                    return hours + ':' + minutes;
                };

                var storeOpentimeStr = convertTime(timeStr);
                var storeEndtimeStr = convertTime(endtimeStr);

                var dt = new Date();
                var currentTime = dt.getHours().toString().padStart(2, '0') + ":" + dt.getMinutes().toString().padStart(2, '0');
                var todaydate = new Date();
                console.log('todaydate out'+todaydate);
                    console.log('selectedDateObj out'+selectedDateObj);
                // You must define `todaydate` somewhere before this block
                if (
                    typeof todaydate !== "undefined" &&
                    todaydate instanceof Date &&
                    selectedDateObj instanceof Date &&
                    todaydate.toDateString() === selectedDateObj.toDateString()
                ) {
                    console.log('todaydate '+todaydate);
                    console.log('selectedDate '+selectedDate);
                    var storeStartDate = new Date(`${selectedDate}T${storeOpentimeStr}`);
                    var currentDateTime = new Date(`${selectedDate}T${currentTime}`);

                    if (currentDateTime < storeStartDate) {
                        var option = document.createElement('option');
                        option.value = timeSlots;
                        option.textContent = timeSlots;
                        timeSelect.appendChild(option);
                    }
                } else {
                    var option = document.createElement('option');
                    option.value = timeSlots;
                    option.textContent = timeSlots;
                    timeSelect.appendChild(option);
                }
            });
        }
    });

    dateSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
    dateSelect.insertAdjacentHTML('beforeend', createDateOptions());

    timeSelect.addEventListener('change', function () {
        var selectedTime = timeSelect.value;
        if (selectedTime !== '') {
            var installerTypeValue = installerTypeSelect.value;
            var installerLocationValue = installerLocations.value;
            var installerDateValue = dateSelect.value;
            var installerTimeValue = timeSelect.value;
            if (installerTypeValue && installerLocationValue && installerDateValue && installerTimeValue) {
                var installerId = installerLocationValue;
                document.getElementById('klever-loader').classList.remove('hidden');
                fetch(BASE_URL + 'storelocator/ajax/setinstaller', {
                    headers: {
                        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                    },
                    body: new URLSearchParams({
                        form_key: hyva.getFormKey(),
                        pickup_store: installerId,
                        pickup_date: installerDateValue,
                        pickup_time: installerTimeValue,
                        uenc: btoa(window.location.href)
                    }),
                    method: "POST",
                    mode: "cors",
                    credentials: "include",
                })
                .then(response => response.json())
                .then(data => {
                    
                    document.getElementById('klever-loader').classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('klever-loader').classList.add('hidden');
                });
            } else {
                console.log('Some values are missing.');
            }
        }
    });

    function toggleDisplay(el, hide) {
        if (el) {
            el.style.display = hide ? 'none' : '';
        }
    }

    function createDateOptions() {
        var options = [];
        var currentDate = new Date();

        for (var i = 0; i < 15; i++) {
            var date = new Date();
            date.setDate(currentDate.getDate() + i);
            
            // Disable Sundays
            // if (date.getDay() === 0) {
            //     continue;
            // }

            // Skip today if it's before 6 PM
            if (i === 0 && currentDate.getHours() < 18) {
                continue;
            }

            // Hide tomorrow if it's after 6 PM
            if (i === 1 && currentDate.getHours() >= 18) {
                continue;
            }

            var formattedDate = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
            options.push('<option value="' + formattedDate + '">' + formattedDate + '</option>');
        }

        return options.join('');
    }
};
