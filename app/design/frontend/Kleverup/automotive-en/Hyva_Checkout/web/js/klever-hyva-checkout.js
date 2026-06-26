window.addEventListener('alpine:init', () => {
    Alpine.directive('numeric-only', (el) => {
        el.addEventListener('input', () => {
            el.value = el.value.replace(/[^0-9]/g, '');
        });
    });

    Alpine.data('initCheckoutVehicleInfoForm', function () {
        const formValidation = hyva.formValidation(this.$el);

        return Object.assign(formValidation, {
            init() {
                // Initialize all selects - read placeholder from first <option> text (supports translations)
                var makePh = (this.$refs.makeSelect && this.$refs.makeSelect.options[0]) ? this.$refs.makeSelect.options[0].text : 'Select Make';
                var modelPh = (this.$refs.modelSelect && this.$refs.modelSelect.options[0]) ? this.$refs.modelSelect.options[0].text : 'Select Model';
                var yearPh = (this.$refs.yearSelect && this.$refs.yearSelect.options[0]) ? this.$refs.yearSelect.options[0].text : 'Select Year';
                this.initTomSelect('makeSelect', { placeholder: makePh, create: false, sortField: { field: "text", direction: "asc" } });
                this.initTomSelect('modelSelect', { placeholder: modelPh, create: false, sortField: { field: "text", direction: "asc" }  });
                this.initTomSelect('yearSelect', { placeholder: yearPh, create: false, sortField: { field: "text", direction: "asc" }  });
            },

            initTomSelect(refName, options = {}) {
                const el = this.$refs[refName];
                if (!el) {
                    console.warn(`initTomSelect: ref ${refName} not found`);
                    return;
                }

                // If TomSelect instance exists, destroy
                if (el.tomselect) {
                    console.log(`Destroying existing TomSelect on ${refName}`);
                    el.tomselect.destroy();
                }

                console.log(`Initializing TomSelect on ${refName}`);
                // create new instance with dropdown_input plugin for searchable dropdown
                var ts = new TomSelect(el, Object.assign({
                    allowEmptyOption: true,
                    placeholder: 'Select option',
                    plugins: ['dropdown_input'],
                    controlInput: null,
                }, options));
                ts.control.classList.add('form-select', 'w-full');
            },

            loadModels() {
                const selectedMake = this.$refs.makeSelect?.value;
                const modelSelect = this.$refs.modelSelect;
                const yearSelect = this.$refs.yearSelect;
                const loader = document.getElementById('klever-loader');
                if (loader) loader.classList.remove('hidden');

                fetch(`${BASE_URL}hyvacheckoutvehicleinfo/ajax/getmodeldropdown`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: new URLSearchParams({
                        form_key: hyva.getFormKey(),
                        make: selectedMake,
                        uenc: btoa(window.location.href)
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        console.log('loadModels response:', data.response);
                        if (modelSelect) {
                            // ✅ DESTROY any existing Tom Select first
                            if (modelSelect.tomselect) {
                                modelSelect.tomselect.destroy();
                            }

                            // ✅ Clear and rebuild the options list
                            const temp = document.createElement('div');
                            temp.innerHTML = `<select>${data.response}</select>`;
                            const newOptions = temp.querySelector('select').options;

                            modelSelect.innerHTML = '';
                            [...newOptions].forEach(opt => modelSelect.appendChild(opt));

                            // ✅ Explicitly reset the select value to the first option (usually '')
                            modelSelect.value = '';

                            // ✅ Wait for DOM to update before initializing TomSelect again
                            this.$nextTick(() => {
                                this.initTomSelect('modelSelect', { placeholder: (this.$refs.modelSelect && this.$refs.modelSelect.options[0]) ? this.$refs.modelSelect.options[0].text : 'Select Model' });

                                // 🔍 Debug: Print updated options to confirm they exist
                                console.log('Updated modelSelect options:', [...this.$refs.modelSelect.options].map(o => o.text));
                            });
                        }
                        if (yearSelect) {
                            yearSelect.innerHTML = data.response;
                            this.$nextTick(() => {
                                this.initTomSelect('yearSelect', { placeholder: (this.$refs.yearSelect && this.$refs.yearSelect.options[0]) ? this.$refs.yearSelect.options[0].text : 'Select Year' });
                            });
                        }
                    })
                    .catch(err => console.error('loadModels error:', err))
                    .finally(() => {
                        if (loader) loader.classList.add('hidden');
                    });
            },

            loadYears() {
                const selectedMake = this.$refs.makeSelect?.value;
                const selectedModel = this.$refs.modelSelect?.value;
                const yearSelect = this.$refs.yearSelect;
                const loader = document.getElementById('klever-loader');
                if (loader) loader.classList.remove('hidden');

                fetch(`${BASE_URL}hyvacheckoutvehicleinfo/ajax/getyeardropdown`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: new URLSearchParams({
                        form_key: hyva.getFormKey(),
                        make: selectedMake,
                        model: selectedModel,
                        uenc: btoa(window.location.href)
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        console.log('loadYears response:', data.response);
                      /*  if (yearSelect) {
                            yearSelect.innerHTML = data.response;
                            this.$nextTick(() => {
                                this.initTomSelect('yearSelect', { placeholder: (this.$refs.yearSelect && this.$refs.yearSelect.options[0]) ? this.$refs.yearSelect.options[0].text : 'Select Year' });
                            });
                        } */

                        if (yearSelect) {

                            // ✅ DESTROY any existing Tom Select first
                            if (yearSelect.tomselect) {
                                yearSelect.tomselect.destroy();
                            }
                            // ✅ Clear and rebuild the options list
                            const temp = document.createElement('div');
                            temp.innerHTML = `<select>${data.response}</select>`;
                            const newOptions = temp.querySelector('select').options;

                            yearSelect.innerHTML = '';
                            [...newOptions].forEach(opt => yearSelect.appendChild(opt));

                            // ✅ Explicitly reset the select value to the first option (usually '')
                            yearSelect.value = '';

                            // ✅ Wait for DOM to update before initializing TomSelect again
                            this.$nextTick(() => {
                                this.initTomSelect('yearSelect', { placeholder: (this.$refs.yearSelect && this.$refs.yearSelect.options[0]) ? this.$refs.yearSelect.options[0].text : 'Select Year' });

                                // 🔍 Debug: Print updated options to confirm they exist
                                console.log('Updated yearSelect options:', [...this.$refs.yearSelect.options].map(o => o.text));
                            });

                            
                        }













                        
                    })
                    .catch(err => console.error('loadYears error:', err))
                    .finally(() => {
                        if (loader) loader.classList.add('hidden');
                    });
            },

            submitCheckoutVehicleInfo() {
                this.validate()
                    .then(() => {
                        // Valid form
                    })
                    .catch((invalid) => {
                        if (invalid.length > 0) {
                            invalid[0].focus();
                        }
                    });
            },

            saveYearSelection() {
                const selectedYear = this.$refs.yearSelect?.value;
                const vehiclePlate = this.$refs.plateInput?.value;
                const loader = document.getElementById('klever-loader');
                if (loader) loader.classList.remove('hidden');

                fetch(`${BASE_URL}hyvacheckoutvehicleinfo/ajax/selectedyear`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: new URLSearchParams({
                        form_key: hyva.getFormKey(),
                        year: selectedYear,
                        plate: vehiclePlate,
                        uenc: btoa(window.location.href)
                    })
                })
                    .then(res => res.json())
                    .catch(err => console.error('saveYearSelection error:', err))
                    .finally(() => {
                        if (loader) loader.classList.add('hidden');
                    });
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = window.location.origin + '/';
    }

    window.addEventListener('checkout:step:loaded', () => {
        if ('storepickup_storepickup' && document.getElementById('shipping-method-list')) {
            window.dispatchEvent(
                new CustomEvent('checkout:shipping:method-activate', {
                    detail: {
                        method: 'storepickup_storepickup'
                    }
                })
            );
        }
    }, { once: true });

    window.addEventListener('load', function () {
        document.querySelector("#shipping-method-storepickup")?.click();
    });

});
