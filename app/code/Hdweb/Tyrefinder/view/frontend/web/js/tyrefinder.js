document.addEventListener('DOMContentLoaded', () => {
    window.getheight = function(widthValue, label, type) {
        document.getElementById('frontWidthLabel-hidden').value = label;
        document.getElementById('frontWidthValue-hidden').value = widthValue;
        document.getElementById('klever-loader').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getheight', {
            headers: {
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                type: type,
                width: widthValue,
                uenc: btoa(window.location.href)
            }),
            method: "POST",
            mode: "cors",
            credentials: "include",
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('allHeightOptions').innerHTML = data.response;
            document.querySelector('.tyrefinder-front-width').classList.add('hidden');
            document.querySelector('.tyrefinder-front-height').classList.remove('hidden');
            var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
            frontSelected.querySelector('.width').textContent = label;
            
            document.getElementById('klever-loader').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('klever-loader').classList.add('hidden');
        });
    };

    window.getrim = function(heightValue, label, type) {
        document.getElementById('frontHeightLabel-hidden').value = label;
        document.getElementById('frontHeightValue-hidden').value = heightValue;
        var widthValue = document.getElementById('frontWidthValue-hidden').value;
        document.getElementById('klever-loader').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getrim', {
            headers: {
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                type: type,
                width: widthValue,
                height: heightValue,
                uenc: btoa(window.location.href)
            }),
            method: "POST",
            mode: "cors",
            credentials: "include",
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('allRimOptions').innerHTML = data.response;
            document.querySelector('.tyrefinder-front-height').classList.add('hidden');
            document.querySelector('.tyrefinder-front-rim').classList.remove('hidden');
            var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
            frontSelected.querySelector('.height').textContent = " /"+label;

            document.getElementById('klever-loader').classList.add('hidden');

        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('klever-loader').classList.add('hidden');
        });
    };

    window.selectRim = function(rimValue, label) {
        document.getElementById('frontRimValue-hidden').value = rimValue;
        document.getElementById('frontRimLabel-hidden').value = label;
        document.querySelector('.tyrefinder-front-rim').classList.add('hidden');
        document.querySelector('.tyre-finder-size-modal .search-final-step').classList.remove('hidden');
        
        var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
        var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
        frontSelected.querySelector('.rim').textContent = " R"+label;



        frontSelected.querySelector('.edit').classList.remove('hidden');
        
        if (window.isRearSizeSelected()) {
            rearSelected.querySelector('.edit').classList.remove('hidden');
            document.querySelector('.tyre-finder-size-modal #tyre-size-search-full-form').classList.add('hidden');
        }else{
            document.querySelector('.diff-rear-size').classList.remove('hidden');
            document.querySelector('.tyre-finder-size-modal #tyre-size-search-full-form').classList.remove('hidden');
        }
    };

    window._saveFitmentLastSearch = function(type, data) {
        var payload = Object.assign({ type: type, timestamp: Date.now() }, data);
        localStorage.setItem('fitmentLastSearch_' + type, JSON.stringify(payload));
    };

    window.submitTyreSelection = function() {
        var width = document.getElementById('frontWidthLabel-hidden').value;
        var height = document.getElementById('frontHeightLabel-hidden').value;
        var rim = document.getElementById('frontRimLabel-hidden').value;
        var rearWidth = document.getElementById('rearWidthLabel-hidden').value;
        var rearHeight = document.getElementById('rearHeightLabel-hidden').value;
        var rearRim = document.getElementById('rearRimLabel-hidden').value;
        window._saveFitmentLastSearch('bySize', {
            width: width, height: height, rim: rim,
            rearWidth: rearWidth || '', rearHeight: rearHeight || '', rearRim: rearRim || ''
        });
        var form = document.getElementById('finder-size-form');
        var existing = form.querySelector('[name="product_list_limit"]');
        if (rearWidth && rearHeight && rearRim) {
            if (!existing) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_list_limit';
                input.value = '5000';
                form.appendChild(input);
            }
        } else if (existing) {
            existing.remove();
        }
        form.submit();
    };

    window.diffRearSize = function() {
        document.querySelector('.tyre-finder-size-modal .search-final-step').classList.add('hidden');
        document.querySelector('.front-tyre-selection').classList.add('hidden');
        document.querySelector('.rear-tyre-selection').classList.remove('hidden');
        document.querySelector('.tyrefinder-rear-width').classList.remove('hidden');
        document.querySelector('.tyrefinder-rear-height').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-rim').classList.add('hidden');

        var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
        frontSelected.querySelector('.front-tyre-selection-plus-rear-label').classList.remove('hidden');
        //frontSelected.querySelector('.selected-label').classList.add('hidden');
        
        document.querySelector('.tyre-finder-size-modal .rear-selected').classList.remove('hidden');
        
        var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
        
        rearSelected.querySelector('.edit').classList.add('hidden');
    };

    window.getRearheight = function(rearwidthValue, label, type) {
        document.getElementById('rearWidthLabel-hidden').value = label;
        document.getElementById('rearWidthValue-hidden').value = rearwidthValue;
        document.getElementById('klever-loader').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getheight', {
            headers: {
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                type: type,
                width: rearwidthValue,
                uenc: btoa(window.location.href)
            }),
            method: "POST",
            mode: "cors",
            credentials: "include",
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('allRearHeightOptions').innerHTML = data.response;
            document.querySelector('.tyrefinder-rear-width').classList.add('hidden');
            document.querySelector('.tyrefinder-rear-height').classList.remove('hidden');

            var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
            rearSelected.querySelector('.width').textContent = label;

            document.getElementById('klever-loader').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('klever-loader').classList.add('hidden');
        });
    };

    window.getRearrim = function(rearheightValue, label, type) {
        document.getElementById('rearHeightLabel-hidden').value = label;
        document.getElementById('rearHeightValue-hidden').value = rearheightValue;
        var rearwidthValue = document.getElementById('rearWidthValue-hidden').value;
        var rear_width = document.getElementById('rearWidthLabel-hidden').value;
        document.getElementById('klever-loader').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getrim', {
            headers: {
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                type: type,
                width: rearwidthValue,
                height: rearheightValue,
                uenc: btoa(window.location.href)
            }),
            method: "POST",
            mode: "cors",
            credentials: "include",
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('allRearRimOptions').innerHTML = data.response;
            document.querySelector('.tyrefinder-rear-height').classList.add('hidden');
            document.querySelector('.tyrefinder-rear-rim').classList.remove('hidden');

            var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
            rearSelected.querySelector('.height').textContent = " /"+label;

            document.getElementById('klever-loader').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('klever-loader').classList.add('hidden');
        });
    };

    window.selectRearRim = function(rimValue, label) {
        document.getElementById('rearRimValue-hidden').value = rimValue;
        document.getElementById('rearRimLabel-hidden').value = label;
        document.querySelector('.diff-rear-size').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-rim').classList.add('hidden');
        document.querySelector('.tyre-finder-size-modal .search-final-step').classList.remove('hidden');
        document.querySelector('.tyre-finder-size-modal #tyre-size-search-full-form').classList.add('hidden');
        var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
        rearSelected.querySelector('.rim').textContent = "\u00A0R" + label;
        rearSelected.querySelector('.edit').classList.remove('hidden');
        rearSelected.querySelector('.delete').classList.remove('hidden');
    };

    window.getmodel = function(value, label) {
        document.getElementById('klever-loader2').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getmodel', {
            headers: {
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                make: value,
                uenc: btoa(window.location.href)
            }),
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('vehicle_make_hidden').value = value;
            document.querySelector('.tyrefinder-brand').classList.add('hidden');
            document.querySelector('.tyrefinder-brand').classList.remove('active');
            document.querySelector('.tyrefinder-model').classList.remove('hidden');
            document.querySelector('.tyrefinder-model').classList.add('active');
            document.querySelector(".tyrefinder-vehicle-selected .make").textContent = label;
            document.querySelector(".tyre-finder-vehicle-modal #vehicle-search").value = "";
            document.getElementById('allModels').innerHTML = data.response;

            document.getElementById('klever-loader2').classList.add('hidden');
        })
        .catch(error => {
            // Handle error here
            document.getElementById('klever-loader2').classList.add('hidden');
        });
    };

    window.getyear = function(value, label) {
        var model = value;
        var make = document.getElementById('vehicle_make_hidden').value;
        document.getElementById('klever-loader2').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getyear', {
            headers: {
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                make: make,
                model: model,
                uenc: btoa(window.location.href)
            }),
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('vehicle_model_hidden').value = value;
            document.querySelector('.tyrefinder-model').classList.add('hidden');
            document.querySelector('.tyrefinder-model').classList.remove('active');
            document.querySelector('.tyrefinder-year').classList.remove('hidden');
            document.querySelector('.tyrefinder-year').classList.add('active');
            document.querySelector(".tyrefinder-vehicle-selected .model").textContent = " / " + label;
            document.querySelector(".tyre-finder-vehicle-modal #vehicle-search").value = "";
            document.getElementById('allYears').innerHTML = data.response;

            document.getElementById('klever-loader2').classList.add('hidden');
        })
        .catch(error => {
            // Handle error here
            document.getElementById('klever-loader2').classList.add('hidden');
        });
    };

    window.getmodifications = function(value, label) {
        var model = document.getElementById('vehicle_model_hidden').value;
        var make = document.getElementById('vehicle_make_hidden').value;
        var year = value;
        document.getElementById('klever-loader2').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getmodifications', {
            headers: {
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                make: make,
                model: model,
                year: year,
                uenc: btoa(window.location.href)
            }),
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('vehicle_year_hidden').value = value;
            document.querySelector('.tyrefinder-year').classList.add('hidden');
            document.querySelector('.tyrefinder-year').classList.remove('active');
            document.querySelector('.tyrefinder-modifications').classList.remove('hidden');
            document.querySelector('.tyrefinder-modifications').classList.add('active');
            document.querySelector(".tyrefinder-vehicle-selected .year").textContent = " / " + label;
            document.querySelector(".tyre-finder-vehicle-modal #vehicle-search").value = "";
            document.getElementById('allModifications').innerHTML = data.response;

            document.getElementById('klever-loader2').classList.add('hidden');
        })
        .catch(error => {
            // Handle error here
            document.getElementById('klever-loader2').classList.add('hidden');
        });
    };

    window.getTyreSizes = function(value, label) {
        var model = document.getElementById('vehicle_model_hidden').value;
        var make = document.getElementById('vehicle_make_hidden').value;
        var year = document.getElementById('vehicle_year_hidden').value;
        var modification = value;
        document.getElementById('klever-loader2').classList.remove('hidden');
        fetch(BASE_URL + 'tyrefinder/ajax/getsearchbymodel', {
            headers: {
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams({
                form_key: hyva.getFormKey(),
                make: make,
                model: model,
                year: year,
                modification: modification,
                uenc: btoa(window.location.href)
            }),
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
        })
        .then(response => response.json())
        .then(data => {
            var enginesTyre = data.enginesTyre.join("");
            document.getElementById('vehicle_engine_hidden').value = value;
            document.querySelector('.tyrefinder-modifications').classList.add('hidden');
            document.querySelector('.tyrefinder-modifications').classList.remove('active');
            document.querySelector('.tyrefinder-tyre-size').classList.remove('hidden');
            document.querySelector('.tyrefinder-tyre-size').classList.add('active');
            document.querySelector(".tyrefinder-vehicle-selected .modification").textContent = " / " + label;
            document.querySelector(".tyre-finder-vehicle-modal #vehicle-search").value = "";
            document.getElementById('allTyreSizes').innerHTML = enginesTyre;
            
            document.getElementById('klever-loader2').classList.add('hidden');
        })
        .catch(error => {
            // Handle error here
            document.getElementById('klever-loader2').classList.add('hidden');
        });
    };

    window.showproduct = function(width, height, rim, rear_width, rear_height, rear_rim) {
        document.getElementById('frontWidthLabel-hidden').value = width;
        document.getElementById('frontHeightLabel-hidden').value = height;
        document.getElementById('frontRimLabel-hidden').value = rim;
        if (rear_width != '' && rear_width !== undefined) {
            document.getElementById('rearWidthLabel-hidden').value = rear_width;
        }

        if (rear_height != '' && rear_height !== undefined) {
            document.getElementById('rearHeightLabel-hidden').value = rear_height;
        }

        if (rear_rim != '' && rear_rim !== undefined) {
            document.getElementById('rearRimLabel-hidden').value = rear_rim;
        }

        var makeValue = document.getElementById('vehicle_make_hidden').value;
        var modelValue = document.getElementById('vehicle_model_hidden').value;
        var yearValue = document.getElementById('vehicle_year_hidden').value;

        // Save vehicle last search
        var makeName = (document.querySelector('.tyrefinder-vehicle-selected .make') || {}).textContent || '';
        var modelName = (document.querySelector('.tyrefinder-vehicle-selected .model') || {}).textContent || '';
        var yearName = (document.querySelector('.tyrefinder-vehicle-selected .year') || {}).textContent || '';
        window._saveFitmentLastSearch('byVehicle', {
            make: makeValue, makeName: makeName.replace(/^\s*\/?\s*/, ''),
            model: modelValue, modelName: modelName.replace(/^\s*\/?\s*/, ''),
            year: yearName.replace(/^\s*\/?\s*/, ''),
            engine: '', engineName: ''
        });

        //document.getElementById('finder-size-form').submit();
        var finderSizeFrom = document.getElementById('finder-size-form');
        var vehicle_make = document.createElement('input');
        vehicle_make.type = 'hidden';
        vehicle_make.name = 's_make';
        vehicle_make.value = makeValue;
        finderSizeFrom.appendChild(vehicle_make);

        var finderSizeFrom = document.getElementById('finder-size-form');
        var vehicle_model = document.createElement('input');
        vehicle_model.type = 'hidden';
        vehicle_model.name = 's_model';
        vehicle_model.value = modelValue;
        finderSizeFrom.appendChild(vehicle_model);

        var finderSizeFrom = document.getElementById('finder-size-form');
        var vehicle_year = document.createElement('input');
        vehicle_year.type = 'hidden';
        vehicle_year.name = 's_year';
        vehicle_year.value = yearValue;
        finderSizeFrom.appendChild(vehicle_year);

        finderSizeFrom.submit();
    };

    window.showWheelProduct = function(width, rim, offset, rear_width, rear_rim, rear_offset) {
        document.getElementById('wheelWidthLabel-hidden').value = width;
        document.getElementById('wheelRimLabel-hidden').value = rim;
        document.getElementById('wheelOffsetLabel-hidden').value = offset;

        if (rear_width != '' && rear_width !== undefined) {
            document.getElementById('rearWheelWidthLabel-hidden').value = rear_width;
        }
        if (rear_rim != '' && rear_rim !== undefined) {
            document.getElementById('rearWheelRimLabel-hidden').value = rear_rim;
        }
        if (rear_offset != '' && rear_offset !== undefined) {
            document.getElementById('rearWheelOffsetLabel-hidden').value = rear_offset;
        }

        // Save vehicle last search for wheels
        var makeName = (document.querySelector('.tyrefinder-vehicle-selected .make') || {}).textContent || '';
        var modelName = (document.querySelector('.tyrefinder-vehicle-selected .model') || {}).textContent || '';
        var yearName = (document.querySelector('.tyrefinder-vehicle-selected .year') || {}).textContent || '';
        window._saveFitmentLastSearch('byVehicle', {
            make: document.getElementById('vehicle_make_hidden').value,
            makeName: makeName.replace(/^\s*\/?\s*/, ''),
            model: document.getElementById('vehicle_model_hidden').value,
            modelName: modelName.replace(/^\s*\/?\s*/, ''),
            year: yearName.replace(/^\s*\/?\s*/, ''),
            engine: '', engineName: ''
        });

        var finderWheelForm = document.getElementById('finder-wheel-form');
        var vehicle_make = document.createElement('input');
        vehicle_make.type = 'hidden';
        vehicle_make.name = 's_make';
        vehicle_make.value = document.getElementById('vehicle_make_hidden').value;
        finderWheelForm.appendChild(vehicle_make);

        var vehicle_model = document.createElement('input');
        vehicle_model.type = 'hidden';
        vehicle_model.name = 's_model';
        vehicle_model.value = document.getElementById('vehicle_model_hidden').value;
        finderWheelForm.appendChild(vehicle_model);

        var vehicle_year = document.createElement('input');
        vehicle_year.type = 'hidden';
        vehicle_year.name = 's_year';
        vehicle_year.value = document.getElementById('vehicle_year_hidden').value;
        finderWheelForm.appendChild(vehicle_year);

        finderWheelForm.submit();
    };

    window.goback = function(backto) {
        resetVehiclesSearchInput();
        if(backto == 'frontwidth'){
            document.querySelector('.tyrefinder-front-width').classList.remove('hidden');
            document.querySelector('.tyrefinder-front-height').classList.add('hidden');
            document.querySelector('.tyrefinder-front-rim').classList.add('hidden');
            document.getElementById('frontWidthLabel-hidden').value = '';
            document.getElementById('frontHeightLabel-hidden').value = '';
        }
        
        if(backto == 'frontheight'){
            document.querySelector('.tyrefinder-front-height').classList.remove('hidden');
            document.querySelector('.tyrefinder-front-rim').classList.add('hidden');
            document.getElementById('frontHeightLabel-hidden').value = '';
            document.getElementById('frontRimLabel-hidden').value = '';
        }

        if(backto == 'rearwidth'){
            document.querySelector('.tyrefinder-rear-width').classList.remove('hidden');
            document.querySelector('.tyrefinder-rear-height').classList.add('hidden');
            document.querySelector('.tyrefinder-rear-rim').classList.add('hidden');
            document.getElementById('rearWidthLabel-hidden').value = '';
            document.getElementById('rearHeightLabel-hidden').value = '';
        }

        if(backto == 'rearheight'){
            document.querySelector('.tyrefinder-rear-height').classList.remove('hidden');
            document.querySelector('.tyrefinder-rear-rim').classList.add('hidden');
            document.getElementById('rearHeightLabel-hidden').value = '';
            document.getElementById('rearRimLabel-hidden').value = '';
        }

        if(backto == 'finalstep_fromrear'){
            document.querySelector('.rear-tyre-selection').classList.add('hidden');
            document.querySelector('.tyrefinder-rear-width').classList.remove('hidden');
            document.querySelector('.tyre-finder-size-modal .search-final-step').classList.remove('hidden');
            document.querySelector('.tyre-finder-size-modal .rear-selected').classList.add('hidden');
            document.querySelector('.diff-rear-size').classList.remove('hidden');
        }

        if(backto == 'allbrands'){
            document.querySelector('.tyrefinder-brand').classList.remove('hidden');
            document.querySelector('.tyrefinder-brand').classList.add('active');
            document.querySelector('.tyrefinder-model').classList.add('hidden');
            document.querySelector('.tyrefinder-model').classList.remove('active');
            document.querySelector(".tyrefinder-vehicle-selected .make").textContent = "";
        }

        if(backto == 'allmodels'){
            document.querySelector('.tyrefinder-model').classList.remove('hidden');
            document.querySelector('.tyrefinder-model').classList.add('active');
            document.querySelector('.tyrefinder-year').classList.add('hidden');
            document.querySelector('.tyrefinder-year').classList.remove('active');
            document.querySelector(".tyrefinder-vehicle-selected .model").textContent = "";
        }

        if(backto == 'allyears'){
            document.querySelector('.tyrefinder-year').classList.remove('hidden');
            document.querySelector('.tyrefinder-year').classList.add('active');
            document.querySelector('.tyrefinder-modifications').classList.add('hidden');
            document.querySelector('.tyrefinder-modifications').classList.remove('active');
            document.querySelector(".tyrefinder-vehicle-selected .year").textContent = "";
        }

        if(backto == 'allmodifications'){
            document.querySelector('.tyrefinder-modifications').classList.remove('hidden');
            document.querySelector('.tyrefinder-modifications').classList.add('active');
            document.querySelector('.tyrefinder-tyre-size').classList.add('hidden');
            document.querySelector('.tyrefinder-tyre-size').classList.remove('active');
            document.querySelector(".tyrefinder-vehicle-selected .modification").textContent = "";
        }
    };

    window.reselectFront = function() {
        emptyFront();
        document.querySelector('.tyrefinder-front-height').classList.add('hidden');
        document.querySelector('.tyrefinder-front-rim').classList.add('hidden');
        document.querySelector('.tyre-finder-size-modal .search-final-step').classList.add('hidden');
        document.querySelector('.tyrefinder-front-width').classList.remove('hidden');
        document.querySelector('.front-tyre-selection').classList.remove('hidden');
        var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
        var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
        frontSelected.querySelector('.edit').classList.add('hidden');
        if (window.isRearSizeSelected()) {
            rearSelected.querySelector('.edit').classList.add('hidden');
            frontSelected.querySelector('.selected-label').classList.add('hidden');
            frontSelected.querySelector('.front-tyre-selection-plus-rear-label').classList.remove('hidden');
        }else{
            
            document.querySelector('.front-tyre-selection-plus-rear-label').classList.add('hidden');
            frontSelected.querySelector('.front-tyre-selection-plus-rear-label').classList.add('hidden');
            frontSelected.querySelector('.selected-label').classList.remove('hidden');
            document.querySelector('.rear-tyre-selection').classList.add('hidden');
            
            rearSelected.classList.add('hidden');
            emptyRear();
        }
        
    };

    window.isFrontSizeSelected = function() {
        var frontWidth = document.getElementById("frontWidthLabel-hidden").value.trim();
        var frontHeight = document.getElementById("frontHeightLabel-hidden").value.trim();
        var frontRim = document.getElementById("frontRimLabel-hidden").value.trim();

        return frontWidth !== '' && frontHeight !== '' && frontRim !== '';
    };

    window.isRearSizeSelected = function() {
        var rearWidth = document.getElementById("rearWidthLabel-hidden").value.trim();
        var rearHeight = document.getElementById("rearHeightLabel-hidden").value.trim();
        var rearRim = document.getElementById("rearRimLabel-hidden").value.trim();

        return rearWidth !== '' && rearHeight !== '' && rearRim !== '';
    };

    window.emptyFront = function() {
        document.getElementById('frontWidthLabel-hidden').value = '';
        document.getElementById('frontWidthValue-hidden').value = '';
        document.getElementById('frontHeightLabel-hidden').value = '';
        document.getElementById('frontHeightValue-hidden').value = '';
        document.getElementById('frontRimValue-hidden').value = '';
        document.getElementById('frontRimLabel-hidden').value = '';
        var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
        frontSelected.querySelector('.width').textContent = '';
        frontSelected.querySelector('.height').textContent = '';
        frontSelected.querySelector('.rim').textContent = '';
        frontSelected.querySelector('.edit').classList.add('hidden');
        document.querySelector('.tyre-finder-size-modal #tyre-size-search-full-form').classList.remove('hidden');
    };

    window.reselectRear = function() {
        emptyRear();
        
        document.querySelector('.rear-tyre-selection').classList.remove('hidden');
        document.querySelector('.tyrefinder-rear-height').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-rim').classList.add('hidden');
        document.querySelector('.tyre-finder-size-modal .search-final-step').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-width').classList.remove('hidden');
        document.querySelector('.tyre-finder-size-modal #tyre-size-search-full-form').classList.remove('hidden');
        console.log('reselectRear selected');
    };

    window.emptyRear = function() {
        document.getElementById('rearWidthLabel-hidden').value = '';
        document.getElementById('rearWidthValue-hidden').value = '';
        document.getElementById('rearHeightLabel-hidden').value = '';
        document.getElementById('rearHeightValue-hidden').value = '';
        document.getElementById('rearRimValue-hidden').value = '';
        document.getElementById('rearRimLabel-hidden').value = '';
        var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
        rearSelected.querySelector('.width').textContent = '';
        rearSelected.querySelector('.height').textContent = '';
        rearSelected.querySelector('.rim').textContent = '';
        rearSelected.querySelector('.edit').classList.add('hidden');
        //rearSelected.classList.add('hidden');
        document.querySelector('.tyre-finder-size-modal #tyre-size-search-full-form').classList.remove('hidden');
    };

    window.removeRear = function() {
        emptyRear();
        if (window.isFrontSizeSelected()) {
            document.querySelector('.diff-rear-size').classList.remove('hidden');
            document.querySelector('.tyre-finder-size-modal .search-final-step').classList.remove('hidden');
        }else{
            document.querySelector('.diff-rear-size').classList.add('hidden');
            document.querySelector('.tyre-finder-size-modal .search-final-step').classList.add('hidden');
        }
        document.querySelector('.tyre-finder-size-modal .rear-selected').classList.add('hidden');
        
        document.querySelector('.rear-tyre-selection').classList.add('hidden');

        var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
        frontSelected.querySelector('.selected-label').classList.remove('hidden');
        frontSelected.querySelector('.front-tyre-selection-plus-rear-label').classList.add('hidden');
    };

    window.resetTyreSizePopup = function() {
        emptyFront();
        emptyRear();
        document.querySelector('.front-tyre-selection').classList.remove('hidden');
        document.querySelector('.tyrefinder-front-height').classList.add('hidden');
        document.querySelector('.tyrefinder-front-rim').classList.add('hidden');
        document.querySelector('.tyrefinder-front-width').classList.remove('hidden');

        document.querySelector('.rear-tyre-selection').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-width').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-height').classList.add('hidden');
        document.querySelector('.tyrefinder-rear-rim').classList.add('hidden');

        var frontSelected = document.querySelector('.tyre-finder-size-modal .front-selected');
        var rearSelected = document.querySelector('.tyre-finder-size-modal .rear-selected');
        frontSelected.querySelector('.front-tyre-selection-plus-rear-label').classList.add('hidden');
        frontSelected.querySelector('.selected-label').classList.remove('hidden');
        rearSelected.classList.add('hidden');
        
    };

    window.resetVehiclesPopup = function() {
        document.getElementById('vehicle_make_hidden').value = '';
        document.getElementById('vehicle_model_hidden').value = '';
        document.getElementById('vehicle_year_hidden').value = '';
        document.getElementById('vehicle_engine_hidden').value = '';
        document.querySelector(".tyrefinder-vehicle-selected .make").textContent = '';
        document.querySelector(".tyrefinder-vehicle-selected .model").textContent = '';
        document.querySelector(".tyrefinder-vehicle-selected .year").textContent = '';
        document.querySelector(".tyrefinder-vehicle-selected .modification").textContent = '';
        document.querySelector('.tyrefinder-model').classList.add('hidden');
        document.querySelector('.tyrefinder-model').classList.remove('active');
        document.querySelector('.tyrefinder-year').classList.add('hidden');
        document.querySelector('.tyrefinder-year').classList.remove('active');
        document.querySelector('.tyrefinder-modifications').classList.add('hidden');
        document.querySelector('.tyrefinder-modifications').classList.remove('active');
        document.querySelector('.tyrefinder-tyre-size').classList.add('hidden');
        document.querySelector('.tyrefinder-tyre-size').classList.remove('active');
        document.querySelector('.tyre-finder-vehicle-modal .search-final-step').classList.add('hidden');
        document.querySelector('.tyre-finder-vehicle-modal .search-final-step').classList.remove('active');
        document.querySelector('.tyrefinder-brand').classList.remove('hidden');
        document.querySelector('.tyrefinder-brand').classList.add('active');
        resetVehiclesSearchInput();
    };

    window.resetVehiclesSearchInput = function() {
        document.querySelectorAll(".searchable-list").forEach(function (list) {
            list.querySelectorAll(".search").forEach(function (item) {
                item.style.display = "";
            });
        });
    };

    var vehicleSearchEl = document.querySelector("#vehicle-search");
    if (vehicleSearchEl) {
        vehicleSearchEl.addEventListener("keyup", function () {
            const value = this.value.toLowerCase();
            const activeList = document.querySelector(".searchable-list.active");
            if (activeList) {
                activeList.querySelectorAll(".search").forEach(function (item) {
                    const text = item.querySelector("span").textContent.toLowerCase();
                    item.style.display = text.includes(value) ? "" : "none";
                });
            }
        });
    }
});
