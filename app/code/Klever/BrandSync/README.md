# Klever_BrandSync

Auto-sync brands, patterns, and SEO meta data from product catalog into the MGS Brand module.

## What It Does

When you import new products with new brands or patterns, this module automatically creates the missing entries in the brand system so the brand pages work without manual admin work.

| Step | What it syncs | Source → Target |
|---|---|---|
| **Brands** | New brand entries | Product `brand` attribute → `mgs_brand` table |
| **Patterns** | New pattern entries with product image | Product `brand` + `pattern` attributes → `mgs_brand_patternmanagement` table |
| **Brand Meta** | Meta title + description for brands | Auto-generated → `mgs_brand.meta_keywords` + `mgs_brand.meta_description` |
| **Tab Titles** | Tab1 + Tab2 titles for brands | Auto-generated → `mgs_brand.tab1_title` + `mgs_brand.tab2_title` |
| **Pattern Meta** | Meta title + description for patterns | Auto-generated → `mgs_brand_patternmanagement.meta_title` + `mgs_brand_patternmanagement.meta_description` |

## How To Use

### After Product Import (Manual)

```bash
php8.2 bin/magento klever:brand:sync
```

Run this after every product CSV import. It is safe to run multiple times — it skips existing entries and never creates duplicates.

### Example Output

```
Starting brand & pattern sync...

=== Sync Complete ===
Brands created:   3
Brands skipped:   0
Patterns created: 15
Patterns skipped: 0
Meta updated:     3
Tabs updated:     3
Pattern meta:     15
```

### Auto Daily Cron (Optional)

Disabled by default. To enable:

**Admin → Stores → Configuration → General → Brand Sync → Enable Daily Cron Sync → Yes**

Runs daily at 3:00 AM. Only processes new/missing entries.

## What Gets Auto-Generated

### New Brand (`mgs_brand`)

When a product has a `brand` attribute value that doesn't exist in `mgs_brand`:

| Field | Value |
|---|---|
| `name` | Brand attribute label (e.g., "Wanli") |
| `url_key` | Auto-generated slug (e.g., "wanli") |
| `small_image` | First product image of that brand |
| `image` | Same as small_image |
| `status` | 1 (enabled) |
| `option_id` | Linked to brand attribute option |
| `brand_category` | From product `parts_category` (Tyres, Battery, etc.) |
| `meta_keywords` | "Buy {Brand} Tyres Online in Dubai, UAE \| TyresCart" |
| `meta_description` | "Shop premium {Brand} tyres online at TyresCart in Dubai..." |
| `tab1_title` | "About {Brand} Tyres" |
| `tab2_title` | "Shop {Brand} Tyres In UAE" |

**Not auto-generated (requires manual input in admin):**
- `description` — brand intro paragraph
- `topbanner_image` — hero banner image
- `tab1_description` — rich HTML content for tab1
- `tab2_description` — rich HTML content for tab2

### New Pattern (`mgs_brand_patternmanagement`)

When a product has a `brand` + `pattern` combo that doesn't exist in the pattern management table:

| Field | Value |
|---|---|
| `brand` | Brand name (e.g., "Wanli") |
| `pattern` | Pattern name (e.g., "SA302") |
| `url_key` | Auto-generated slug (e.g., "sa302") |
| `image` | Product catalog image (e.g., `catalog/product/tyres/wanli-sa302.jpg`) |
| `status` | 1 (enabled) |
| `meta_title` | "Buy {Brand} {Pattern} Tyres Online in UAE \| TyresCart" |
| `meta_description` | "Shop {Brand} {Pattern} tyres in Dubai, Abu Dhabi, and UAE..." |

**Not auto-generated (requires manual input in admin):**
- `short_description` — pattern card description
- `description` — full pattern page description
- `performance_description` — performance details
- `dry`, `wet`, `sport`, `comfort`, `mileage` — performance ratings

## Workflow

### First Time Setup (Already Done)

```bash
php8.2 bin/magento module:enable Klever_BrandSync
php8.2 bin/magento setup:upgrade
php8.2 bin/magento klever:brand:sync
```

### Ongoing — After Each Product Import

```bash
# 1. Import products via CSV (creates new brand/pattern attribute options)
# 2. Run sync to create brand pages and pattern entries
php8.2 bin/magento klever:brand:sync

# 3. (Optional) Go to admin to add rich content for new brands:
#    Admin → Brand → Manage Brands → edit new brand
#    - Add description, banner image, tab1/tab2 content
#
#    Admin → Brand → Pattern Management → edit new patterns
#    - Add short_description, performance ratings
```

### Category-Aware Meta

The module detects `brand_category` and adjusts meta text accordingly:

| Category | Meta Title Example |
|---|---|
| Tyres | "Buy Bridgestone Tyres Online in Dubai, UAE \| TyresCart" |
| Battery | "Buy Bosch Batteries Online in Dubai, UAE \| TyresCart" |
| Motorcycle Tyres | "Buy Metzeler Motorcycle Tyres Online in Dubai, UAE \| TyresCart" |
| Rim Protectors | "Buy Alloygator Rim Protectors Online in Dubai, UAE \| TyresCart" |

## Module Files

```
app/code/Klever/BrandSync/
├── registration.php
├── README.md
├── Console/Command/
│   └── SyncCommand.php            # CLI: php8.2 bin/magento klever:brand:sync
├── Cron/
│   └── SyncBrands.php             # Daily cron (disabled by default)
├── Model/
│   └── BrandPatternSync.php       # Core sync logic (brands, patterns, meta, tabs)
└── etc/
    ├── module.xml
    ├── di.xml                     # CLI command registration
    ├── config.xml                 # Default config (cron disabled)
    ├── crontab.xml                # Cron schedule (3 AM daily)
    └── adminhtml/
        └── system.xml             # Admin config: enable/disable cron
```

## Database Tables Affected

| Table | Action |
|---|---|
| `mgs_brand` | INSERT new brands, UPDATE meta + tab titles |
| `mgs_brand_store` | INSERT store associations for new brands |
| `mgs_brand_patternmanagement` | INSERT new patterns, UPDATE meta title + description |

## Notes

- The sync reads product data (EAV attributes `brand`, `pattern`, `parts_category`, `image`) and writes to MGS Brand tables
- Pattern images are referenced from the product catalog (`catalog/product/tyres/...`) — no file copying needed
- Running the command multiple times is safe — it only processes missing entries
- The cron job checks `klever_brandsync/general/enable_cron` config flag before running
