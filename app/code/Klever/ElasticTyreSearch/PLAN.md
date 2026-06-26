# Enriched Tyre Size Search — Implementation Plan

## Context
**Why:** When a user searches a tyre size (e.g. "19555"), the current search returns simple pills + products. Users in the tyre industry need the full picture: what vehicles fit this size, what staggered pairings exist, how many brands/products are available, and what the price range is — all before clicking.

**What:** Enrich each tyre size result with vehicle compatibility, staggered front/rear pairings, brand/price stats, cheapest & premium options.

**Where:** Backend enrichment in `Klever/ElasticTyreSearch`, data queries in `Klever/VehicleTyresGuide`, frontend rendering in `fitment-bar.phtml`.

**When:** Enrichment runs on every search that returns tyre sizes (max 10 sizes, all cached 24h).

---

## Module Architecture Decision
**Keep `VehicleTyresGuide` and `ElasticTyreSearch` as separate modules.**
- VehicleTyresGuide = data layer (GraphQL, vehicle finder pages)
- ElasticTyreSearch = search layer (OpenSearch, AJAX, enrichment)
- ElasticTyreSearch depends on VehicleTyresGuide's `FlatWheelData` resource model via DI
- Clean separation of concerns, independent versioning

---

## Enriched Response Format (backwards compatible)

```json
{
  "tyresizes": [
    {
      "size": "205/55 R16",
      "url": "…",
      "model_count": 87,
      "sample_vehicles": ["Toyota Camry", "Honda Civic", "BMW 3 Series"],
      "staggered_pairs": [
        {"size": "225/45 R16", "position": "rear", "frequency": 47}
      ],
      "product_count": 118,
      "brand_count": 46,
      "brand_names": ["Continental", "Michelin", "Bridgestone", "Pirelli", "Goodyear"],
      "price_min": 181,
      "price_max": 796,
      "cheapest": {"name": "Roadx RXMotion H12", "price": 181},
      "premium": {"name": "Continental PremiumContact 6", "price": 796}
    }
  ],
  "products": [...], "brands": [...], "blogs": [...], "vehicles": [...], "cms": [...]
}
```

---

## Implementation Checklist

### Step 1: FlatWheelData — Add vehicle stats method
- [x] **File:** `app/code/Klever/VehicleTyresGuide/Model/ResourceModel/FlatWheelData.php`
- [x] Add `getVehicleStatsForSize(int $w, int $h, int $r): array`
  - COUNT(DISTINCT make_slug, model_slug) as model_count
  - 5 sample vehicle names via CONCAT(make_name, ' ', model_name)
  - Matches front OR rear tyre columns
  - Uses existing cache pattern (cacheKey/fromCache/toCache, 24h TTL)
- [x] Verify with: `php8.2 -r` test calling the method for 205/55/16

### Step 2: FlatWheelData — Add staggered pairings method
- [x] **File:** `app/code/Klever/VehicleTyresGuide/Model/ResourceModel/FlatWheelData.php`
- [x] Add `getStaggeredPairings(int $w, int $h, int $r): array`
  - When size is front → find distinct different rear sizes + frequency
  - When size is rear → find distinct different front sizes + frequency
  - ORDER BY frequency DESC, LIMIT 5 per direction
  - Returns: [{size, position, frequency}, ...]
  - Cached 24h
- [x] Verify with: `php8.2 -r` test for 205/55/16 and 225/45/17

### Step 3: ProductStatsProvider — NEW
- [x] **File:** `app/code/Klever/ElasticTyreSearch/Model/ProductStatsProvider.php` (CREATE)
- [x] Constructor injects: `ResourceConnection`, `CacheInterface`, `Magento\Eav\Model\Config`
- [x] Method: `getStatsForTyreSize(string $tyreSizeLabel): array`
  - Resolves tyre_size attribute ID + option ID from label via EAV config
  - Joins catalog EAV tables for: product count, brand count, top 5 brands, price min/max
  - Gets cheapest + premium product names
  - Cached 24h
- [x] Returns: `{product_count, brand_count, brand_names[], price_min, price_max, cheapest{name,price}, premium{name,price}}`
- [x] Verify with: `php8.2 -r` test for "205/55 R16"

### Step 4: TyreSizeEnrichmentService — NEW
- [x] **File:** `app/code/Klever/ElasticTyreSearch/Model/TyreSizeEnrichmentService.php` (CREATE)
- [x] Constructor injects: `FlatWheelData`, `ProductStatsProvider`
- [x] Method: `enrich(array $sizes): array` — takes size strings, returns keyed array
  - `parseTyreSize("205/55 R16")` → {width, height, rim}
  - Calls FlatWheelData::getVehicleStatsForSize() per size
  - Calls FlatWheelData::getStaggeredPairings() per size
  - Calls ProductStatsProvider::getStatsForTyreSize() per size
  - Merges all into single array per size
- [x] Verify with: `php8.2 -r` test enriching ["205/55 R16", "195/55 R15"]

### Step 5: GlobalSearchService — MODIFY
- [x] **File:** `app/code/Klever/ElasticTyreSearch/Model/GlobalSearchService.php`
- [x] Add `TyreSizeEnrichmentService` to constructor injection
- [x] Comment out old simple tyre size building (~lines 98-106)
- [x] Add enriched version: call `enrich($rawSizes)`, merge with {size, url}
- [x] Verify: AJAX endpoint returns enriched JSON

### Step 6: Frontend — MODIFY
- [x] **File:** `app/design/frontend/Kleverup/automotive-en/Magento_Theme/templates/html/header/fitment-bar.phtml`
- [x] Back up file first
- [x] Comment out old `renderTyreSizes()` function
- [x] Add new `renderTyreSizes()` that renders enriched **cards**:
  - Size as clickable header (still selects front/rear for the form)
  - Price range badge: "AED 181 – 796"
  - Stats line: "118 tyres · 46 brands · fits 87 models"
  - Sample vehicles: "Toyota Camry, Honda Civic, BMW 3 Series +84 more"
  - Staggered: "225/45 R16 (rear)"
  - Best value: "Roadx RXMotion H12 – AED 181"
- [x] Change `os-list-tyresizes-drawer` from `flex flex-wrap gap-2` to plain div
- [x] Verify: browser test typing "20555" in drawer

### Step 7: Versioning & Changelog
- [x] Bump `Klever/VehicleTyresGuide` → 1.2.0 (new methods)
- [x] Bump `Klever/ElasticTyreSearch` version if tracked
- [x] Update CHANGELOG.md files

### Step 8: Cache & Deploy
- [x] `rm -rf var/view_preprocessed/ var/page_cache/ var/cache/`
- [x] User runs: `php8.2 bin/magento setup:upgrade && php8.2 bin/magento setup:di:compile`

---

## Performance Budget
- TyreSizeSearch returns max 10 sizes
- Each enrichment: 3 cached MySQL queries on indexed columns
- Cold cache: ~90ms total (10 × 3 × ~3ms) — within 300ms debounce
- Warm cache: ~0ms — all results from Magento cache
- Existing indexes: `idx_front_tire`, `idx_rear_tire` already cover the query columns

## Durability Note
No treadwear/UTQG/durability attributes exist in the product catalog. Skipped for now. Can be added later when product data is enriched with EU tyre labels or UTQG ratings.

---

## Change Log (track failures & plan changes)

| Date | What Changed | Why | Where | Resolution |
|------|-------------|-----|-------|------------|
| 2026-06-18 | Brand attr code `mgs_brand` → `brand` | Attribute code was wrong, brand_count returned 0 | ProductStatsProvider.php | Changed getAttribute('catalog_product', 'brand') |
| 2026-06-18 | Cleared generated/code after adding constructor param | Compiled DI cache had stale 7-param constructor | GlobalSearchService.php | rm -rf generated/code/Klever/ generated/metadata/ |

---

## Key File Paths
```
app/code/Klever/VehicleTyresGuide/Model/ResourceModel/FlatWheelData.php     (MODIFY)
app/code/Klever/ElasticTyreSearch/Model/ProductStatsProvider.php             (CREATE)
app/code/Klever/ElasticTyreSearch/Model/TyreSizeEnrichmentService.php        (CREATE)
app/code/Klever/ElasticTyreSearch/Model/GlobalSearchService.php              (MODIFY)
app/design/frontend/.../header/fitment-bar.phtml                             (MODIFY)
app/code/Klever/VehicleTyresGuide/CHANGELOG.md                              (UPDATE)
app/code/Klever/VehicleTyresGuide/composer.json                              (BUMP)
```
