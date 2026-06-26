# Klever ElasticTyreSearch — How the Search Works

Module path: `app/code/Klever/ElasticTyreSearch`

---

## Overview

This module provides the **global search panel** on the storefront. When a user types into `#magento-search-input` (minimum 3 characters), an AJAX call is fired to a custom endpoint that queries Elasticsearch and returns results across 6 content types in a single response — no page reload.

---

## Request Flow

```
User types in #magento-search-input (≥ 3 chars)
        │
        ▼
global-search.js  (view/frontend/web/js/global-search.js)
        │
        │  GET /elastyresearch/ajax/search?q=<query>
        ▼
Controller\Ajax\Search
        │  strips HTML tags, truncates at 100 chars
        │  runs NormalizeTyreSizeQuery::normalize()
        ▼
Model\GlobalSearchService::search()
        │
        ├── ProductSearch    → ES catalog_product index (getSource, no DB)
        ├── BrandSearch      → ES klever_brand index
        ├── BlogSearch       → ES klever_blog index
        ├── VehicleSearch    → ES klever_vehicle index
        ├── CmsSearch        → ES klever_cms index
        └── TyreSizeSearch   → ES catalog_product index (wildcard on option_text_tyre_size)
        │
        ▼
JSON response → global-search.js renders tabbed panel
```

---

## AJAX Endpoint

| Property | Value |
|----------|-------|
| Route    | `GET /elastyresearch/ajax/search?q=<query>` |
| File     | `Controller/Ajax/Search.php` |
| Min query length | 2 chars (after normalization) |
| Max query length | 100 chars (truncated in controller) |

### Response shape

```json
{
  "tyresizes": [{ "size": "195/65 R15", "url": "..." }],
  "products":  [{ "id": 123, "name": "...", "url": "...", "image": "...", "price": 350.0, "brand": "Yokohama" }],
  "brands":    [{ "id": 10, "name": "...", "url": "/brand/...", "image": "..." }],
  "blogs":     [{ "id": 5, "title": "...", "url": "...", "image": "..." }],
  "vehicles":  [{ "id": 7, "name": "...", "url": "...", "image": "..." }],
  "cms":       [{ "id": 2, "title": "...", "url": "..." }]
}
```

---

## Tyre Size Query Normalization

**File:** `Plugin/NormalizeTyreSizeQuery.php`

Runs on every query — both the AJAX endpoint and Magento's native `QueryFactory`. Converts compact user input to canonical format before hitting Elasticsearch.

| Input | Output |
|-------|--------|
| `19565` | `195/65` |
| `19565r15` | `195/65 R15` |
| `1956515` | `195/65 R15` |
| `195/65r15` | `195/65 R15` |
| `195/65 15` | `195/65 R15` |
| `195 65 r15` | `195/65 R15` |

Non-tyre-size strings (e.g. "yokohama") pass through unchanged.

The plugin also hooks `QueryFactory::afterGet()` so the normalized query is used for Magento's native catalog search results page too.

---

## Search Classes

### `Model/GlobalSearchService.php`

Orchestrates all 6 searches and assembles the final response. Injects `StoreManagerInterface` to build absolute media and base URLs.

- Products: calls `ProductSearch`, receives full data from ES `_source` — **no database queries**
- Tyre sizes: normalises the query again before calling `TyreSizeSearch`
- Brands/Blogs/Vehicles: prefixes relative image paths with the media base URL

### `Model/Search/Elastic/ProductSearch.php`

Uses ElasticSuite's `Builder` + `SearchEngine` with the `klever_product_search` request container (defined in `etc/elasticsuite_search_request.xml`). This applies ElasticSuite's built-in stock and visibility filters automatically.

Calls `$document->getSource()` to read fields directly from the ES index — **no `ProductRepository::getById()` calls**.

Fields extracted from `_source`:

| Field | ES key | Notes |
|-------|--------|-------|
| Name | `name` | May be array; normalised via `scalar()` helper |
| URL | `url_key` | Combined with store base URL + `.html` |
| Image | `image` | Combined with `{media_url}catalog/product{image}` |
| Price | `price[].final_price` | Nested per customer-group array; uses group 0 (guest) |
| Brand | `option_text_mgs_brand` | ElasticSuite option-text field naming convention |

> **Note:** ElasticSuite sometimes stores text fields as single-element arrays in `_source`. The `scalar()` private method unwraps these before returning.

### `Model/Search/Elastic/TyreSizeSearch.php`

Uses the raw ElasticSuite `ClientInterface` directly (not the Builder). Queries the `quick_search_container` index (same physical index as `catalog_product`) with a **wildcard** on `option_text_tyre_size`.

Pattern built from query: spaces replaced with `*`, `*` appended at end, lowercased.
Example: `"195/65"` → wildcard pattern `"195/65*"`.

Returns distinct, sorted tyre size strings (deduped in PHP).

### `Model/Search/Elastic/BrandSearch.php` / `BlogSearch.php` / `VehicleSearch.php` / `CmsSearch.php`

All follow the same pattern:
- Use ElasticSuite `Builder` + `SearchEngine` with their respective request container (`klever_brand_search`, `klever_blog_search`, etc.)
- Call `$document->getSource()` for field data
- Return flat arrays ready for JSON

---

## Elasticsearch Indices

### Custom indices (defined in `etc/elasticsuite_indices.xml`)

| Index identifier | Type name | Searchable fields | Other fields |
|-----------------|-----------|-------------------|--------------|
| `klever_brand` | `brand` | `name` | `url_key`, `image` |
| `klever_blog` | `post` | `title`, `short_content` | `url_key`, `image`, `status` |
| `klever_vehicle` | `vehicle` | `name` | `slug`, `logo_image`, `is_active` |
| `klever_cms` | `page` | `title`, `content` | `identifier`, `is_active` |

### Product index

Uses Magento/ElasticSuite's native `catalog_product` index. No custom mapping needed — tyre size and brand option text fields are indexed by ElasticSuite's attribute pipeline.

### Indexers (reindex commands)

```bash
php bin/magento indexer:reindex klever_brand_indexer
php bin/magento indexer:reindex klever_blog_indexer
php bin/magento indexer:reindex klever_vehicle_indexer
php bin/magento indexer:reindex klever_cms_indexer
# Product index uses native Magento indexer:
php bin/magento indexer:reindex catalogsearch_fulltext
```

---

## ElasticSuite Search Request Containers (`etc/elasticsuite_search_request.xml`)

| Container name | Index | Filters applied |
|----------------|-------|----------------|
| `klever_product_search` | `catalog_product` | Stock, VisibleInSearch |
| `klever_brand_search` | `klever_brand` | none |
| `klever_blog_search` | `klever_blog` | none |
| `klever_vehicle_search` | `klever_vehicle` | none |
| `klever_cms_search` | `klever_cms` | none |

---

## Frontend JS (`view/frontend/web/js/global-search.js`)

- Binds to `#magento-search-input` keyup (debounced)
- Detects if query looks like a tyre size: `/^[\d\s\/\.rRcCxX]+$/`
- Fires AJAX to `/elastyresearch/ajax/search?q=<query>`
- Renders a tabbed panel (left: tab list with counts, right: results)
- Auto-selects the Tyre Sizes tab if query matches tyre-size pattern
- Arrow keys navigate tabs/results; Escape closes panel

Styles: `view/frontend/web/css/global-search.css`
Config (search URL injected as JS variable): `view/frontend/templates/global-search-config.phtml`

---

## Result Limits

| Content type | Default limit |
|-------------|---------------|
| Products | 5 |
| Brands | 4 |
| Blogs | 3 |
| Vehicles | 6 |
| CMS pages | 2 |
| Tyre sizes | 10 |

Limits are passed as the `$limit` argument in `GlobalSearchService::search()`.

---

## Key Design Decisions

**Products come entirely from ES (no DB queries)**
Previously, `ProductSearch` returned only IDs and `GlobalSearchService` called `ProductRepository::getById()` for each one (N+1 DB queries per search). Now `getSource()` is used to read `name`, `url_key`, `image`, `price`, and `option_text_mgs_brand` directly from the ES document — same approach as `BrandSearch`.

**Tyre size normalization runs in two places**
1. `NormalizeTyreSizeQuery` plugin — rewrites the query on Magento's `QueryFactory` so the native search results page also benefits
2. `GlobalSearchService` — normalizes again before calling `TyreSizeSearch` specifically

**ES fields can be arrays**
ElasticSuite stores some text fields (e.g. `name`) as single-element arrays in `_source`. `ProductSearch::scalar()` unwraps these before returning.


we are on tyre industry saling online for the searsh we need to add extra     
  layre for user. What we have tire sizes, combination of tire sizes front and  
  rear, vahical data (mostly from https://developer.wheel-size.com/             
  https://api.wheel-size.com/v2/swagger/) we also have products, blogs, pages. I wnat to use this on search result with suggestions. Like if I search tire    
  size output will shows result with suggestion of possible front/rear sizes.  
  and this size will fits on how many vahicles or model, this size will         
  available on which tire brands, what are costly what are cheapest, what are   
  more durable etc. so user have exact picutre what he want.