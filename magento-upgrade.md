# Magento Upgrade Changes Log

## Upgrade Summary
- **Date:** 2026-03-16
- **Magento:** 2.4.7-p3 → 2.4.8-p4
- **Hyva Theme:** 1.3.14 → 1.4.4
- **Hyva Theme Module:** 1.3.14 → 1.4.4
- **Hyva Checkout:** 1.3.2 → 1.3.9

---

## 1. composer.json — Version Updates

**File:** `composer.json`

### Changes:
- Updated `version` field from `2.4.7-p3` to `2.4.8-p4`
- Updated Hyva constraints to match installed versions:

| Package | Before | After |
|---------|--------|-------|
| `magento/product-community-edition` | 2.4.8 | 2.4.8-p4 |
| `hyva-themes/magento2-default-theme` | ^1.3.14 | ^1.4.4 |
| `hyva-themes/magento2-default-theme-csp` | ^1.3 | ^1.4.4 |
| `hyva-themes/magento2-hyva-checkout` | ^1.3.2 | ^1.3.9 |
| `hyva-themes/magento2-smile-elasticsuite` | ^1.2 | ^1.2.7 |
| `hyva-themes/magento2-theme-module` | ^1.3.14 | ^1.4.4 |

---

## 2. PHP 8.1+ Deprecation Fix — strpos() null parameter

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/slider-filter-js.phtml`

### Issue:
`strpos()` no longer accepts `null` as first parameter in PHP 8.1+.

### Change (Line 17):
```php
// Before
$isPriceSlider = (strpos($block->getDataRole(), 'price') !== false);

// After
$isPriceSlider = (strpos((string) $block->getDataRole(), 'price') !== false);
```

---

## 3. CSP Nonce Registration — Missing `registerInlineScript()`

After Hyva 1.4.4 upgrade, inline `<script>` tags require CSP nonce registration via `$hyvaCsp->registerInlineScript()`.

### 3a. collapse.phtml

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/page/js/plugins/collapse.phtml`

### Changes:
- Added `use Hyva\Theme\ViewModel\HyvaCsp;`
- Added `/** @var HyvaCsp $hyvaCsp */`
- Added `<?php $hyvaCsp->registerInlineScript() ?>` after closing `</script>` tag

---

### 3b. hyva-accordion.phtml

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/page/js/plugins/hyva-accordion.phtml`

### Changes:
- Added `use Hyva\Theme\ViewModel\HyvaCsp;`
- Added `/** @var HyvaCsp $hyvaCsp */`
- Added `<?php $hyvaCsp->registerInlineScript() ?>` after closing `</script>` tag

---

### 3c. ajax-cart.phtml

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/page/js/plugins/ajax-cart.phtml`

### Changes:
- Added `use Hyva\Theme\ViewModel\HyvaCsp;`
- Added `/** @var HyvaCsp $hyvaCsp */`
- Added `<?php $hyvaCsp->registerInlineScript() ?>` after both `</script>` tags (2 inline script blocks)

---

## 4. Missing Alpine Method — submitCheckoutVehicleInfo

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_Checkout/web/js/klever-hyva-checkout.js`

### Issue:
The `submitCheckoutVehicleInfo()` method was missing from the Alpine component `initCheckoutVehicleInfoForm`, causing Alpine CSP error: "Alpine is unable to interpret the following expression".

### Change:
Added the missing method back (restored from backup `klever-hyva-checkout_BK19thSept2025G.js`):

```javascript
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
```

---

## 5. Hyva Checkout API v1.phtml — Replaced with Vendor Version

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_Checkout/templates/page/js/api/v1.phtml`

### Issue:
The custom override was based on old Hyva Checkout 1.3.2 (883 lines). The new vendor version (1.3.9) has 1300 lines with critical new features including the `priority()` API method. This caused `Cannot read properties of undefined (reading 'priority')` errors.

### Change:
- Backed up old file to `v1.phtml.bkp-old`
- Replaced with vendor version from `vendor/hyva-themes/magento2-hyva-checkout/src/view/frontend/templates/page/js/api/v1.phtml`
- Re-applied custom vehicle info validation in `order.place()` method:

```javascript
// Added at the start of order.place() method:
/* start extra added for checkout vehicle form */
const submitBtn = document.getElementById("checkout-vehicleinfo-submit-btn");
if (submitBtn) {
    submitBtn.click();
}

const form = document.getElementById("vehicle-information-form");

if (form && !form.checkValidity()) {
    form.reportValidity();
    await hyvaCheckout.navigation.executeTasks();
    return false;
}
/* end extra added for checkout vehicle form */
```

---

## 6. SmileElasticsuite Filter Templates — Replaced with Vendor Versions

After the Hyva SmileElasticsuite upgrade (1.2.5 → 1.2.7), the filter JS architecture changed. The old approach created unique per-filter functions (`initSmileAttibute_UNIQUEID()`), while the new approach uses a single global function with options passed as parameters.

### 6a. attribute-filter-js.phtml — Replaced with vendor version

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/attribute-filter-js.phtml`

- Old file backed up to `attribute-filter-js.phtml.bkp-old`
- Replaced with vendor version which defines `initSmileAttribute(options)` (single global function)
- Vendor version includes `$hyvaCsp->registerInlineScript()` for CSP compliance

### 6b. attribute.phtml — Updated to match vendor approach

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/attribute.phtml`

- Old file backed up to `attribute.phtml.bkp-old`
- Removed old `getChildBlock('attribute-filter-js')` approach
- Changed `x-data` from `initSmileAttibute_UNIQUEID()` to `initSmileAttribute(options)` (vendor approach)
- Kept custom scrollbar CSS classes (`max-h-[167px] overflow-y-auto` with custom scrollbar styling)

### 6c. slider-filter-js.phtml — Replaced with vendor version

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/slider-filter-js.phtml`

- Replaced with vendor version which defines `rangeSlider(options, isPriceSlider)` (with parameters)
- Vendor version includes `$hyvaCsp->registerInlineScript()` for CSP compliance

### 6d. Layout XML — Added missing JS blocks

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/layout/hyva_catalog_category_view_type_layered.xml`

- Added `attribute-filter-js` and `slider-filter-js` blocks to `before.body.end` container
- These blocks were present in the vendor layout but missing from the custom override

---

## 7. Mobile Menu — setActiveMenu null reference fix

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header/menu/mobile.phtml`

### Issue:
`item.closest('li.level-0').querySelector('a.level-0')` could return `null`, causing `classList` error.

### Change (Line 381):
```javascript
// Before
item.closest('li.level-0').querySelector('a.level-0').classList.add('underline');

// After
item.closest('li.level-0').querySelector('a.level-0')?.classList.add('underline');
```

---

## 8. Product Image Cache — 404 after upgrade

### Issue:
Product images on cart and category pages returned 404. The upgrade changed the image cache hash, so the old cache directory (e.g. `0d6bbfa5e5141e0e4f1fffd16d79012e`) no longer existed.

### Fix:
```bash
php bin/magento catalog:images:resize
```
This regenerated all product image cache with the new hash. Original images in `pub/media/catalog/product/` were intact.

---

## 9. Cart Page — tyreSizeFullSearch null reference fix

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header.phtml`

### Issue:
`document.getElementById("tyre-size-search-full-head")` returns `null` on pages where the element doesn't exist (e.g. cart), causing `classList` errors.

### Change:
Added null checks with `if (tyreSizeFullSearch)` and optional chaining `?.` for all references to this element.

---

## 10. CSP Nonce Registration — Additional files

Added `$hyvaCsp->registerInlineScript()` after inline `<script>` tags in the following files:

### Theme Templates:
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header.phtml` (3 inline scripts)
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header/menu/desktop.phtml` (2 inline scripts)
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header/menu/mobile.phtml` (1 inline script)

### Hdweb Custom Modules:
- `app/code/Hdweb/Vehicles/view/frontend/templates/vehicles.phtml`
- `app/code/Hdweb/Vehicles/view/frontend/templates/home-vehicles.phtml`
- `app/code/Hdweb/Vehicles/view/frontend/templates/model.phtml`
- `app/code/Hdweb/WarrantyClaim/view/frontend/templates/form.phtml` (3 inline scripts)
- `app/code/Hdweb/Tyrefinder/view/frontend/templates/hyva_tyre_finder_listingpage.phtml`
- `app/code/Hdweb/Tyrefinder/view/frontend/templates/hyva_tyre_finder.phtml`
- `app/code/Hdweb/Tyrefinder/view/frontend/templates/tyre-size-search.phtml`

---

## 11. Footer — Inline onclick handlers and CSP nonce fixes

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/footer.phtml`

### Issue:
- Inline `onclick="..."` attributes (e.g. `SendInquiryOpenModal()`, `RequestOpenModal()`, `RequestModalClose()`, `EnquiryFormClose()`) are blocked by CSP `script-src-attr` policy
- 6 inline `<script>` blocks missing CSP nonce registration

### Changes:
- Converted all `onclick="..."` to Alpine `@click="..."` handlers
- Added `$hyvaCsp->registerInlineScript()` after all 6 inline `</script>` tags
- Added `use Hyva\Theme\ViewModel\HyvaCsp;` and type hint

---

## 12. CSP Whitelist — Missing connect-src for Cloudflare

**File:** `app/code/Hdweb/Core/etc/csp_whitelist.xml`

### Issue:
Swiper JS library tries to load its source map from `cdnjs.cloudflare.com`, which was allowed in `script-src` but missing from `connect-src` policy, causing CSP violation.

### Change:
Added `*.cloudflare.com` to the `connect-src` policy:
```xml
<value id="cloudflare-connect" type="host">*.cloudflare.com</value>
```

---

## 13. Footer Collapse — Inline x-data to Alpine.data() conversion

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/footer/collapse.phtml`

### Issue:
Inline `x-data="{...}"` object expression with methods can't be evaluated by the CSP-friendly Alpine build, causing `isMobile` and `forEach` errors.

### Change:
- Moved inline `x-data` object to `Alpine.data('footerCollapse...')` registered in a `<script>` block
- Added `$hyvaCsp->registerInlineScript()` for CSP compliance
- Each collapse instance gets a unique component name via `md5($title)`

---

## 14. Checkout Telephone Input — CSP-compatible numeric-only filter

**Files:**
- `app/design/frontend/Kleverup/automotive-en/Hyva_Checkout/templates/form/field/text.phtml`
- `app/design/frontend/Kleverup/automotive-en/Hyva_Checkout/web/js/klever-hyva-checkout.js`

### Issue:
`x-on:input="this.value = this.value.replace(/[^0-9]/g, '')"` uses inline string expression which CSP-friendly Alpine can't evaluate.

### Change:
- Registered a custom Alpine directive `x-numeric-only` in `klever-hyva-checkout.js`:
```javascript
Alpine.directive('numeric-only', (el) => {
    el.addEventListener('input', () => {
        el.value = el.value.replace(/[^0-9]/g, '');
    });
});
```
- Replaced `x-on:input="..."` with `x-numeric-only` directive on the telephone input

---

## 15. Footer Collapse — Template literal and CSP fixes

**Files:**
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/footer/collapse.phtml`
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/web/js/footer-collapse.js` (new)
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/js/footer-collapse-loader.phtml` (new)
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/layout/default.xml`

### Issue:
- Inline `x-data` with methods couldn't register via `Alpine.data()` inside CSP-blocked inline scripts
- Template literal `` `height: ${height}px` `` in `:style` binding caused "invalid escape sequence" error in Alpine evaluator
- `isMobile` and `getContentStyle` undefined because the component never registered

### Changes:
- Moved `Alpine.data('footerCollapse')` to an external JS file (`footer-collapse.js`)
- Loaded via `head.additional` block in `default.xml` to ensure it registers before Alpine initializes
- Replaced template literal `:style` with `getContentStyle()` method using string concatenation
- Moved all event handlers (`resize`, `visibilitychange`, `accordion-close-all`, `$watch`) into `init()` method
- `collapse.phtml` now uses `x-data="footerCollapse(open, title)"` with parameters

---

## 16. Footer Forms — Spread syntax incompatible with Alpine evaluator

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/footer.phtml`

### Issue:
`x-data="{...hyva.formValidation($el), ...initCallBackForm()}"` spread syntax caused "invalid escape sequence" errors on ALL pages (footer loads globally).

### Changes:
- Converted callback form: `x-data` changed to `initCallBackFormWithValidation` (registered via `Alpine.data()`)
- Converted enquiry form: `x-data` changed to `initEnquiryFormWithValidation` (registered via `Alpine.data()`)
- Both use `Object.assign(hyva.formValidation(this.$el), {...})` inside JS instead of spread syntax in HTML
- Converted `x-data="{ showSocial: false }"` to `Alpine.data('initSocialButtons')`

---

## 17. Blog Images — 404 due to store code in media URL

**File:** `app/code/MGS/Blog/view/frontend/templates/home_blog.phtml`

### Issue:
Blog thumbnail images returned 404 because `$this->getBaseUrl()` includes `/en/` store code, making URLs like `/en/media/mgs_blog/...` instead of `/media/mgs_blog/...`.

### Change:
Replaced `$this->getBaseUrl() . 'media/mgs_blog/'` with `$mediaUrl . 'mgs_blog/'` where `$mediaUrl` is obtained from `$storeManager->getStore()->getBaseUrl(URL_TYPE_MEDIA)` which returns the correct `/media/` path without store code.

---

## 18. Additional CSP Nonce Registrations

- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/collapsible.phtml` — added `$hyvaCsp->registerInlineScript()`
- `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header-search.phtml` — added `$hyvaCsp->registerInlineScript()`
- `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/view.phtml` — added `$hyvaCsp->registerInlineScript()`

---

## 19. Elfsight CSP Whitelist

**File:** `app/code/Hdweb/Core/etc/csp_whitelist.xml`

### Changes:
- Added `elfsightcdn.com` (bare domain) to `script-src`
- Added `*.elfsight.com` and `static.elfsight.com` to `connect-src`

---

## 20. Tabby Payment Module — Upgraded to 7.0.2

### Upgrade Summary:
| Package | Before | After |
|---------|--------|-------|
| `tabby/m2-payments` (metapackage) | 5.0.1 | **7.0.2** |
| `tabby/m2-checkout` | 5.0.1 | **6.4.4** |
| `tabby/m2-feed` | (new) | **1.0.2** |

### Process:
- Backed up old module to `var/backups/Tabby_BKP_20260317/`
- Downloaded latest packages via Composer to temp directory
- Replaced `app/code/Tabby/Checkout/` with new version (6.4.4)
- Added new `app/code/Tabby/Feed/` module (1.0.2)
- Ran `setup:upgrade`, `setup:di:compile`, `static-content:deploy`, `cache:flush`

### Note:
`tabby/m2-payments:7.0.2` is a metapackage — it requires `tabby/m2-checkout:6.4.4` and `tabby/m2-feed:1.0.2` as sub-packages. The module was installed in `app/code` (not via Composer) as the team may have custom modifications.

---

## 21. Tamara PHP SDK — Missing Dependency

### Issue:
`Class "Tamara\Notification\NotificationService" not found` when saving Tamara webhook config.

### Fix:
Installed the missing SDK package:
```bash
composer require tamara-solution/php-sdk:^2.0.3 -W
```
Note: `firebase/php-jwt` was downgraded from v7.0.3 to v6.11.1 to satisfy SDK requirements.

---

## 22. Hdweb Purchaseorder — Missing Database Tables

### Issue:
Admin Sales Order view threw `Table 'b2btyre-new.purchase_order_item' doesn't exist` error. The `Hdweb_Purchaseorder` module's tables were lost during DB migration.

### Fix:
Manually created the missing tables (`po_vendor`, `purchase_order`, `purchase_order_item`, `po_vendor_fitment`) with all columns from both `InstallSchema.php` and `UpgradeSchema.php`.

### Follow-up Fix:
After tables were created, a second error appeared: `Column 'order_id' in where clause is ambiguous` — both `purchase_order_item` and `purchase_order` tables have an `order_id` column, and the collection JOIN made it ambiguous.

**File:** `app/code/Hdweb/Purchaseorder/Model/ResourceModel/Purchaseorderitem/Collection.php`

Added filter map:
```php
$this->addFilterToMap('order_id', 'main_table.order_id');
```

---

## 23. TotalPay Gateway — PHP 8.x explode() Fix & Hyva Checkout Integration

### 23a. PHP 8.x Deprecation Fix

**File:** `app/code/TotalPay/Gateway/Model/Config.php`

### Issue:
`explode()` received `null` instead of string for `payment_method_types` and `transaction_types` config values (both empty), causing fatal error in PHP 8.x.

### Change:
```php
// Before
explode(',', $this->getValue('payment_method_types'))
explode(',', $this->getValue('transaction_types'))

// After
explode(',', (string)$this->getValue('payment_method_types'))
explode(',', (string)$this->getValue('transaction_types'))
```

### 23b. Improved Error Logging

**File:** `app/code/TotalPay/Gateway/Model/Method/Checkout.php`

### Change:
```php
// Before (error message swallowed — debug() expects array as 2nd param)
$this->_logger->debug('exceptionORDER', $e->getMessage());

// After
$this->_logger->debug('exceptionORDER: ' . $e->getMessage());
```

### 23c. Hyva Checkout PlaceOrderService

### Issue:
After placing order with TotalPay, customer was redirected to success page instead of TotalPay payment gateway. TotalPay stores its redirect URL in the checkout session, but Hyva's default PlaceOrderService doesn't read it.

### New Files:
- `app/code/TotalPay/Gateway/Model/Magewire/Payment/PlaceOrderService.php`

### Modified Files:
- `app/code/TotalPay/Gateway/etc/frontend/di.xml` — registered PlaceOrderService for `totalpay_checkout`

### PlaceOrderService reads redirect URL from checkout session:
```php
public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
{
    $redirectUrl = $this->checkoutSession->getTotalPayGatewayCheckoutRedirectUrl();
    if ($redirectUrl) {
        return $redirectUrl;
    }
    return 'totalpay/checkout/index';
}
```

---

## 24. Tabby Checkout — Hyva Checkout PlaceOrderService

### Issue:
Same as TotalPay — after placing order with Tabby, customer was redirected to success page instead of Tabby payment gateway.

### New Files:
- `app/code/Tabby/Checkout/Model/Magewire/Payment/PlaceOrderService.php`

Note: The backup (v5.0.1) already had this file with redirect to `/tabby/redirect/index`. The new version (v6.4.4) was missing it. Recreated with redirect to `tabby/redirect`.

### Modified Files:
- `app/code/Tabby/Checkout/etc/frontend/di.xml` — registered PlaceOrderService for `tabby_installments`, `tabby_checkout`, `tabby_cc_installments`

### Known Issue:
Tabby API returns `"status": "rejected"` with `"rejection_reason": "not_available"` for merchant code `tyrescart_uae`. The old v5.0.1 had merchant_code hardcoded to `'default'`. The new v6.4.4 generates it dynamically via `MerchantCodeProvider`. The `installments` product may need to be enabled on Tabby's dashboard for the correct merchant code, or the merchant code mapping may need adjustment.

---

## Commands Run After Changes (2.4.8-p4 Upgrade)

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f --area=frontend --theme=Kleverup/automotive-en
php bin/magento cache:flush
```

---
---

# Hyva Theme & Checkout Upgrade

## Upgrade Summary
- **Date:** 2026-05-21
- **Magento:** 2.4.8-p4 (unchanged)
- **PHP:** 8.2.30 (unchanged)

### Package Versions

| Package | Before | After |
|---------|--------|-------|
| `hyva-themes/magento2-default-theme` | 1.4.4 | 1.4.6 |
| `hyva-themes/magento2-default-theme-csp` | 1.4.4 | 1.4.6 |
| `hyva-themes/magento2-theme-module` | 1.4.4 | 1.4.6 |
| `hyva-themes/magento2-hyva-checkout` | 1.3.9 | 1.3.10 |

---

## 1. Composer Update

### Commands:
```bash
# Backup composer files
cp composer.json composer.json.bkp-pre-hyva-upgrade
cp composer.lock composer.lock.bkp-pre-hyva-upgrade

# Update package requirements
composer require --no-update hyva-themes/magento2-default-theme:^1.4.6 \
  hyva-themes/magento2-default-theme-csp:^1.4.6 \
  hyva-themes/magento2-theme-module:^1.4.6 \
  hyva-themes/magento2-hyva-checkout:^1.3.10

# Run update for Hyva packages only
composer update hyva-themes/magento2-default-theme \
  hyva-themes/magento2-default-theme-csp \
  hyva-themes/magento2-theme-module \
  hyva-themes/magento2-hyva-checkout --no-interaction
```

---

## 2. Custom Override Verification

### 2a. Hyva Checkout API v1.phtml — No vendor changes in 1.3.10

**File:** `app/design/frontend/Kleverup/automotive-en/Hyva_Checkout/templates/page/js/api/v1.phtml`

The only difference between the theme override and the new vendor version (1.3.10) is the custom vehicle info validation code added during the 2.4.8-p4 upgrade. No changes needed — override is safe.

Custom code retained in `order.place()` method:
```javascript
/* start extra added for checkout vehicle form */
const submitBtn = document.getElementById("checkout-vehicleinfo-submit-btn");
if (submitBtn) {
    submitBtn.click();
}
const form = document.getElementById("vehicle-information-form");
if (form && !form.checkValidity()) {
    form.reportValidity();
    await hyvaCheckout.navigation.executeTasks();
    return false;
}
/* end extra added for checkout vehicle form */
```

### 2b. SmileElasticsuite Filter Templates — No vendor changes

**Files verified (identical to vendor):**
- `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/attribute-filter-js.phtml`
- `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/attribute.phtml`
- `app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/slider-filter-js.phtml`

No changes needed — all overrides are safe.

---

## 3. Commands Run After Hyva Upgrade

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f --area=frontend --theme=Kleverup/automotive-en
php bin/magento cache:flush
```

All commands completed successfully with no errors.
