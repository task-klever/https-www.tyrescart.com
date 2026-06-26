require([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    // reload customer data to update header
    var sections = ['customer'];
    customerData.invalidate(sections);
    customerData.reload(sections, true);
});