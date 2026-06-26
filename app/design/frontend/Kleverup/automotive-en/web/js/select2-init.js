document.addEventListener('DOMContentLoaded', function () {
    var interval = setInterval(function () {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            try {
                jQuery('#vInfoVehicleMake').select2({
                    placeholder: 'Select Make',
                    width: '100%' // or whatever width you need
                });
            } catch (e) {
                console.error('Select2 initialization error:', e);
            }
            clearInterval(interval);
        }
    }, 100);
});