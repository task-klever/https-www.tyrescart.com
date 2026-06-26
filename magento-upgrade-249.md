# Magento 2.4.9 Upgrade Guide

## Upgrade Summary
- **Date:** 2026-05-21 (Planned)
- **Magento:** 2.4.8-p4 → 2.4.9
- **PHP:** 8.2.30 → 8.4 (already changed on CloudPanel)
- **Hyva Theme:** 1.4.4 → 1.4.6
- **Hyva Theme Module:** 1.4.4 → 1.4.6
- **Hyva Checkout:** 1.3.9 → 1.3.10
- **Hyva SmileElasticsuite:** 1.2.7.1 → latest compatible
- **SmileElasticsuite:** 2.11.17.1 → latest 2.11.x

---

## Current Server Stack

| Component | Current Version | Required for 2.4.9 | Status |
|-----------|----------------|---------------------|--------|
| PHP | 8.2.30 (CLI) / 8.4 (Panel) | 8.3 / 8.4 / 8.5 | ⚠️ CLI still using 8.2 — needs switching |
| MySQL | 8.0.41 | 8.4 LTS or MariaDB 11.4 | ❌ BLOCKER — MySQL 8.0 dropped |
| Elasticsearch | 7.17.27 | ❌ Removed in 2.4.9 | ❌ BLOCKER — Must migrate to OpenSearch 3.x |
| Redis | 6.0.16 | Valkey 8 (or Redis 7.4+) | ⚠️ Redis deprecated, Valkey 8 recommended |
| Composer | 2.7.9 | 2.9.3+ | ❌ Must upgrade |
| RabbitMQ | Not installed | 4.1 (if needed) | ✅ Not used — skip |

---

## PRE-UPGRADE: Infrastructure Changes (Must Do BEFORE Magento Upgrade)

### Step 1: Full Backup

```bash
# Database backup
mysqldump -u b2btyreusr-new -p b2btyre-new > /home/kleverup-auto/backups/b2btyre-new_pre249_$(date +%Y%m%d).sql

# Files backup
tar -czf /home/kleverup-auto/backups/auto_kleverup_pre249_$(date +%Y%m%d).tar.gz \
  --exclude='var/cache' --exclude='var/page_cache' --exclude='generated' \
  /home/kleverup-auto/htdocs/auto.kleverup.com/

# Composer files backup
cp composer.json composer.json.bkp-pre249
cp composer.lock composer.lock.bkp-pre249
```

---

### Step 2: Upgrade MySQL 8.0 → 8.4 LTS (BLOCKER)

MySQL 8.0 is **dropped** in Magento 2.4.9. Must upgrade to MySQL 8.4 LTS.

```bash
# Check current version
mysql --version
# mysql Ver 8.0.41

# On CloudPanel (Ubuntu 22.04):
# 1. Stop MySQL
sudo systemctl stop mysql

# 2. Backup all databases
mysqldump --all-databases --single-transaction -u root -p > /home/kleverup-auto/backups/all_databases_pre_mysql84.sql

# 3. Remove MySQL 8.0 and install 8.4 LTS
# Follow CloudPanel documentation for MySQL upgrade
# Or manually:
sudo apt remove mysql-server mysql-client
# Add MySQL 8.4 APT repository and install

# 4. Verify after upgrade
mysql --version
# Should show 8.4.x
```

**⚠️ IMPORTANT:** Test schema compatibility after MySQL upgrade. Run `mysqlcheck --all-databases --check` after upgrade.

---

### Step 3: Migrate Elasticsearch 7.17 → OpenSearch 3.x (BLOCKER)

Elasticsearch is **completely removed** in Magento 2.4.9. Must migrate to OpenSearch 3.x.

**Note:** SmileElasticsuite is being used as the search engine. ElasticSuite 2.11.x supports OpenSearch.

```bash
# 1. Stop Elasticsearch
sudo systemctl stop elasticsearch

# 2. Install OpenSearch 3.x
# Follow OpenSearch installation guide for Ubuntu
# https://opensearch.org/docs/latest/install-and-configure/install-opensearch/

# 3. Configure OpenSearch to run on same port (9200) or update Magento config
# OpenSearch default port: 9200

# 4. After OpenSearch is running, update Magento config:
php bin/magento config:set catalog/search/engine elasticsuite
# ElasticSuite handles the connection — update ElasticSuite config:
php bin/magento config:set smile_elasticsuite_core_base_settings/es_client/servers localhost:9200

# 5. Verify OpenSearch is running
curl -s localhost:9200
# Should show OpenSearch 3.x response

# 6. Reindex
php bin/magento indexer:reindex catalogsearch_fulltext
```

---

### Step 4: Upgrade Redis 6.0 → Valkey 8 (Recommended) or Redis 7.4+

Redis is **deprecated** in Magento 2.4.9. Valkey 8 is the official replacement (same wire protocol).

Currently using Redis for:
- **Cache:** database 7 (`Cm_Cache_Backend_Redis`)
- **Sessions:** database 8

```bash
# Option A: Install Valkey 8 (recommended — same protocol, drop-in replacement)
sudo systemctl stop redis

# Install Valkey 8
# https://valkey.io/download/

# Valkey uses same port 6379 and same protocol — no config changes needed in env.php

# Option B: Upgrade Redis to 7.4+
# Less recommended as Redis is deprecated in Magento 2.4.9

# Verify after upgrade
valkey-server --version  # or redis-server --version
```

**Note:** `env.php` references `Cm_Cache_Backend_Redis` — this works with Valkey as it uses the same protocol. No `env.php` changes needed.

---

### Step 5: Switch PHP CLI to 8.4

The CloudPanel panel shows PHP 8.4, but CLI still runs PHP 8.2.30.

```bash
# Check current CLI PHP
php -v
# PHP 8.2.30

# Update CLI to use PHP 8.4
sudo update-alternatives --set php /usr/bin/php8.4

# Verify
php -v
# Should show PHP 8.4.x

# Also update php-fpm if needed
# Check CloudPanel settings to ensure 8.4 is active for the site
```

---

### Step 6: Upgrade Composer to 2.9.3+

```bash
# Current: 2.7.9
composer self-update

# Verify
composer --version
# Should show 2.9.3 or higher
```

---

## MAGENTO UPGRADE: Step-by-Step

### Step 7: Enable Maintenance Mode

```bash
cd /home/kleverup-auto/htdocs/auto.kleverup.com
php bin/magento maintenance:enable
```

---

### Step 8: Update composer.json for Magento 2.4.9

**⚠️ IMPORTANT:** Magento 2.4.9 uses `require-commerce` command instead of `composer require`.

```bash
# Update the root-update-plugin first
composer require magento/composer-root-update-plugin=^2.0.4 --no-update

# Use require-commerce to update Magento core
composer require-commerce --no-update "magento/product-community-edition=2.4.9"
```

---

### Step 9: Update Hyva Theme + Checkout + Dependencies

```bash
# Update Hyva packages
composer require --no-update hyva-themes/magento2-default-theme:^1.4.6
composer require --no-update hyva-themes/magento2-default-theme-csp:^1.4.6
composer require --no-update hyva-themes/magento2-theme-module:^1.4.6
composer require --no-update hyva-themes/magento2-hyva-checkout:^1.3.10

# Update SmileElasticsuite (check latest 2.11.x compatible with 2.4.9)
composer require --no-update smile/elasticsuite:~2.11.18
composer require --no-update hyva-themes/magento2-smile-elasticsuite:^1.2.7

# Update other dependencies if needed
composer require --no-update ngenius/ngenius-common:^1.3
```

---

### Step 10: Run Composer Update

```bash
# Run the full update
composer update --no-interaction

# If you get conflicts, try with -W flag to allow dependency downgrades
composer update --no-interaction -W
```

**Expected:** ~40 magento/* dependency bumps, Symfony 7.4 upgrade, and all Hyva packages updated.

---

### Step 11: Update composer.json Version Field

```bash
# Edit composer.json — change version field
# "version": "2.4.8-p4" → "version": "2.4.9"
```

---

### Step 12: Run Magento Setup

```bash
# Remove generated code
rm -rf generated/code/* generated/metadata/*
rm -rf var/cache/* var/page_cache/* var/view_preprocessed/*

# Run setup upgrade
php bin/magento setup:upgrade

# Compile DI
php bin/magento setup:di:compile

# Deploy static content
php bin/magento setup:static-content:deploy -f --area=frontend --theme=Kleverup/automotive-en
php bin/magento setup:static-content:deploy -f --area=adminhtml

# Flush cache
php bin/magento cache:flush

# Reindex all
php bin/magento indexer:reindex
```

---

### Step 13: Regenerate Product Image Cache

```bash
# Same as last upgrade — image cache hash changes
php bin/magento catalog:images:resize
```

---

### Step 14: Disable Maintenance Mode

```bash
php bin/magento maintenance:disable
```

---

## POST-UPGRADE: Known Issues to Check & Fix

### Issue 1: Laminas MVC Removed — Check Custom Modules

Magento 2.4.9 **removes Laminas MVC**. Any custom module that extends Laminas MVC classes will break.

**Check these custom modules:**
```bash
# Search for Laminas MVC usage in custom code
grep -rn "Laminas\\\\Mvc" app/code/
grep -rn "use Laminas" app/code/
```

Modules to check:
- `app/code/Hdweb/*`
- `app/code/TotalPay/*`
- `app/code/Tabby/*`
- `app/code/MGS/*`

---

### Issue 2: TinyMCE Replaced by HugeRTE

TinyMCE is removed from admin panel. If any custom admin pages use TinyMCE customizations, they need to be updated for HugeRTE.

```bash
# Check for TinyMCE references
grep -rn "tinymce" app/code/ --include="*.php" --include="*.phtml" --include="*.js" --include="*.xml"
```

---

### Issue 3: Zend_Cache Replaced by Symfony Cache

```bash
# Check for Zend_Cache usage
grep -rn "Zend_Cache" app/code/
grep -rn "Zend\\\\Cache" app/code/
```

---

### Issue 4: PHP 8.4 Deprecations — Implicit Nullable Parameters

PHP 8.4 deprecates implicit nullable parameters. This was common in older code:

```php
// Deprecated in PHP 8.4
function foo(string $bar = null) {}

// Correct
function foo(?string $bar = null) {}
```

```bash
# Check for implicit nullable in custom modules
grep -rn "function.*string \$.*= null" app/code/
grep -rn "function.*array \$.*= null" app/code/
grep -rn "function.*int \$.*= null" app/code/
```

---

### Issue 5: CSP Nonce — Verify All Inline Scripts Still Work

Since we already fixed CSP nonce registration in the 2.4.8-p4 upgrade, verify all `$hyvaCsp->registerInlineScript()` calls still work with the new Hyva 1.4.6.

**Files to verify (from previous upgrade):**
- `Magento_Theme/templates/page/js/plugins/collapse.phtml`
- `Magento_Theme/templates/page/js/plugins/hyva-accordion.phtml`
- `Magento_Theme/templates/page/js/plugins/ajax-cart.phtml`
- `Magento_Theme/templates/html/header.phtml`
- `Magento_Theme/templates/html/header/menu/desktop.phtml`
- `Magento_Theme/templates/html/header/menu/mobile.phtml`
- `Magento_Theme/templates/html/footer.phtml`
- `Magento_Theme/templates/html/footer/collapse.phtml`
- `Magento_Theme/templates/html/header-search.phtml`
- `Magento_Theme/templates/html/collapsible.phtml`
- All `app/code/Hdweb/*/view/frontend/templates/*.phtml` files with inline scripts

---

### Issue 6: Hyva Checkout API v1.phtml — Check for Vendor Changes

Last upgrade we replaced `Hyva_Checkout/templates/page/js/api/v1.phtml` with the vendor version and re-applied custom vehicle info validation. Check if the new Hyva Checkout 1.3.10 has changes to this file.

```bash
# Compare current override with new vendor version
diff app/design/frontend/Kleverup/automotive-en/Hyva_Checkout/templates/page/js/api/v1.phtml \
     vendor/hyva-themes/magento2-hyva-checkout/src/view/frontend/templates/page/js/api/v1.phtml
```

If the vendor version changed, replace and re-apply the custom vehicle info validation code:
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

---

### Issue 7: SmileElasticsuite Filter Templates — Verify Overrides

Check if the updated SmileElasticsuite vendor templates changed:
```bash
# Compare overrides with vendor versions
diff app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/attribute-filter-js.phtml \
     vendor/hyva-themes/magento2-smile-elasticsuite/src/view/frontend/templates/catalog/layer/filter/js/attribute-filter-js.phtml

diff app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/attribute.phtml \
     vendor/hyva-themes/magento2-smile-elasticsuite/src/view/frontend/templates/catalog/layer/filter/attribute.phtml

diff app/design/frontend/Kleverup/automotive-en/Hyva_SmileElasticsuite/templates/catalog/layer/filter/js/slider-filter-js.phtml \
     vendor/hyva-themes/magento2-smile-elasticsuite/src/view/frontend/templates/catalog/layer/filter/js/slider-filter-js.phtml
```

---

### Issue 8: TotalPay Gateway — Verify PHP 8.4 Compatibility

We fixed `explode()` null issues in the 2.4.8-p4 upgrade. Check for any additional PHP 8.4 deprecations:

```bash
grep -rn "strpos\|strstr\|substr\|explode\|implode\|trim\|strtolower\|strtoupper" app/code/TotalPay/ --include="*.php" | grep -v "(string)"
```

---

### Issue 9: Tabby Checkout — Verify Module Works with 2.4.9

Tabby Checkout v6.4.4 was installed in app/code during the 2.4.8-p4 upgrade. Verify compatibility:

```bash
grep -rn "Laminas\|Zend_" app/code/Tabby/ --include="*.php"
```

---

### Issue 10: getEscaper() Removed

Magento 2.4.9 removes the deprecated `getEscaper()` method. Check custom templates:

```bash
grep -rn "getEscaper()" app/code/ app/design/frontend/Kleverup/
```

---

### Issue 11: Footer Collapse External JS — Verify Still Loads

The `footer-collapse.js` was created during the 2.4.8-p4 upgrade and loaded via `default.xml`. Verify it still loads correctly with Hyva 1.4.6.

**File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/web/js/footer-collapse.js`
**Layout:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/layout/default.xml`

---

### Issue 12: Blog Module (MGS/Blog) — Verify Image Fix Still Works

The blog image URL fix from 2.4.8-p4 upgrade should still work, but verify:

**File:** `app/code/MGS/Blog/view/frontend/templates/home_blog.phtml`

---

## POST-UPGRADE: Testing Checklist

### Frontend Pages:
- [ ] Homepage loads correctly
- [ ] Category pages with layered navigation (ElasticSuite filters)
- [ ] Product detail pages
- [ ] Product image cache (if 404, run `php bin/magento catalog:images:resize`)
- [ ] Cart page (check `tyreSizeFullSearch` null reference)
- [ ] Search functionality (ElasticSuite + OpenSearch 3)
- [ ] Mobile menu (`setActiveMenu` null fix)
- [ ] Footer collapse accordion
- [ ] Footer forms (callback & enquiry)
- [ ] Blog page images

### Checkout:
- [ ] Guest checkout flow
- [ ] Logged-in customer checkout
- [ ] Vehicle information form on checkout
- [ ] Telephone numeric-only input
- [ ] TotalPay payment — redirect to gateway
- [ ] Tabby payment — redirect to gateway
- [ ] N-Genius payment
- [ ] Tamara payment

### Admin:
- [ ] Admin login works
- [ ] Sales order grid loads
- [ ] Product edit page (HugeRTE editor instead of TinyMCE)
- [ ] Category management
- [ ] Purchase order view (Hdweb_Purchaseorder)

### CSP (Content Security Policy):
- [ ] No CSP violations in browser console
- [ ] All inline scripts have nonce
- [ ] No blocked `onclick` handlers
- [ ] Elfsight widget loads
- [ ] Cloudflare resources load

---

## Version Map — Before vs After

| Package | Before (2.4.8-p4) | After (2.4.9) |
|---------|-------------------|---------------|
| `magento/product-community-edition` | 2.4.8-p4 | 2.4.9 |
| `hyva-themes/magento2-default-theme` | 1.4.4 | ^1.4.6 |
| `hyva-themes/magento2-default-theme-csp` | 1.4.4 | ^1.4.6 |
| `hyva-themes/magento2-theme-module` | 1.4.4 | ^1.4.6 |
| `hyva-themes/magento2-hyva-checkout` | 1.3.9 | ^1.3.10 |
| `hyva-themes/magento2-smile-elasticsuite` | 1.2.7.1 | ^1.2.7 (latest) |
| `smile/elasticsuite` | 2.11.17.1 | ~2.11.18+ |
| PHP | 8.2.30 | 8.4.x |
| MySQL | 8.0.41 | 8.4 LTS |
| Elasticsearch | 7.17.27 | ❌ Removed |
| OpenSearch | N/A | 3.x |
| Redis | 6.0.16 | Valkey 8 |
| Composer | 2.7.9 | 2.9.3+ |

---

## Commands to Run After All Changes

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f --area=frontend --theme=Kleverup/automotive-en
php bin/magento setup:static-content:deploy -f --area=adminhtml
php bin/magento cache:flush
php bin/magento indexer:reindex
php bin/magento catalog:images:resize
```

---

## Rollback Plan

If the upgrade fails:

```bash
# 1. Enable maintenance mode
php bin/magento maintenance:enable

# 2. Restore database
mysql -u b2btyreusr-new -p b2btyre-new < /home/kleverup-auto/backups/b2btyre-new_pre249_YYYYMMDD.sql

# 3. Restore composer files
cp composer.json.bkp-pre249 composer.json
cp composer.lock.bkp-pre249 composer.lock
composer install --no-interaction

# 4. Restore generated files
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush

# 5. Disable maintenance mode
php bin/magento maintenance:disable
```

**⚠️ Note:** If you already upgraded MySQL/OpenSearch, rollback also requires downgrading those services or restoring from a full server snapshot.
