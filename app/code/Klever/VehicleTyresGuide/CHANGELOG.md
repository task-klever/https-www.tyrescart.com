# Changelog

All notable changes to `Klever_VehicleTyresGuide` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and
this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-06-23

### Changed
- `getModifications()` query now filters by `JSON_CONTAINS(available_years, ...)`
  and `JSON_CONTAINS(regions, '"medm"')` to eliminate duplicate rows from other
  regions. Previously used `start_year`/`end_year` range matching with a
  `year_ranges` JSON fallback, which returned duplicates across regions.
- Removed PHP-level deduplication loop (was collapsing different tyre sizes for
  the same modification into a single row).

## [1.2.0] - 2026-06-18

### Added
- `getVehicleStatsForSize(int $w, int $h, int $r)` — returns model/make count and
  sample vehicle names for a given tyre size (front or rear match).
- `getStaggeredPairings(int $w, int $h, int $r)` — returns front/rear staggered
  size combinations with frequency, sorted by popularity.
- Both methods use the existing 24h cache layer.

## [1.1.0] - 2026-06-18

### Added
- Frontend AJAX endpoint `klever-vehicle/ajax/tyreSizes` returning all distinct
  tyre sizes from `flat_wheel_data` (front + rear combined). Replaces the slower
  `tyrefinder/ajax/gettyresize` endpoint which queried the product catalog via EAV.
- `getTyreSizes()` method in `FlatWheelData` resource model.
- Frontend route config (`etc/frontend/routes.xml`) with front name `klever-vehicle`.

## [1.0.2] - 2026-06-18

### Changed
- Added MySQL indexes on `flat_wheel_data` for columns used by GraphQL queries:
  `idx_make_slug_name (make_slug, make_name)`,
  `idx_model_slug (make_slug, model_slug)`,
  `idx_make_model_year_slug (make_slug, model_slug, start_year, end_year)`.
- Performance improvement: `getMakes` 388ms → 1ms, `getModels` 392ms → 31ms,
  `getModifications` 998ms → 4ms. Previously these queries did full table scans
  because existing indexes were on `make_id`/`model_id` columns (unused by the module).

## [1.0.1] - 2026-06-16

### Fixed
- `searchByTyreSize()` now returns results for same-size (non-staggered) fitments
  where rear tyre columns are NULL in `flat_wheel_data`. Previously the query
  required all six tyre columns (front + rear) to be NOT NULL, which excluded
  every row where the rear values were not populated (front = rear).
- Used `COALESCE(rear_*, front_*)` so rear values fall back to front values when
  NULL, matching the real-world data pattern.

## [1.0.0] - 2026-06-13

### Added
- Initial release. Read-only GraphQL API exposing vehicle tyre and wheel fitment
  data from the externally-managed `flat_wheel_data` table.
- GraphQL queries (all prefixed `kleverVehicle`):
  - `kleverVehicleMakes` — list makes, with ordering/limit/offset.
  - `kleverVehicleModels` — list models for a make, with merged year ranges.
  - `kleverVehicleYears` — production years for a make + model.
  - `kleverVehicleModifications` — full tyre/wheel/engine/fitment specs for a
    make + model + year.
  - `kleverVehicleGenerations` — generation buckets derived from start/end year.
  - `kleverVehicleSearch` — find vehicles compatible with a given tyre size.
- All SQL isolated in `Model/ResourceModel/FlatWheelData.php`; resolvers contain
  no raw SQL. Reads use raw `ResourceConnection` (no ORM models) and are strictly
  read-only — the module never writes to `flat_wheel_data`.
- Per-response caching under the `klever_vehicle` cache tag with a configurable TTL.
- System configuration at **Stores → Configuration → Klever → Vehicle Tyres Guide**
  with `enabled`, `default_region`, and `cache_ttl`, supporting default/website/store
  scope. `acl.xml` backs the `Klever_VehicleTyresGuide::config` resource.
- Read-only **Module Version** field in admin config, sourced from `composer.json`.
- No `db_schema.xml` by design — `flat_wheel_data` is imported externally and must
  never be managed (or dropped) by Magento's declarative schema.
