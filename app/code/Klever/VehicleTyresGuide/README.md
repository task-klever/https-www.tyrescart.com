# Klever_VehicleTyresGuide

Magento 2 module that exposes vehicle tyre and wheel fitment data via GraphQL.
It reads **exclusively** from a pre-existing MySQL table, `flat_wheel_data`,
which is imported externally (a ~250 MB SQL dump) and is **never** managed,
altered, or dropped by Magento. The module is strictly read-only.

- **Vendor:** Klever Tech Solution
- **Package:** `klever/vehicle-tyres-guide`
- **Module name:** `Klever_VehicleTyresGuide`
- **GraphQL prefix:** `kleverVehicle`
- **Current version:** see [`composer.json`](composer.json) and [`CHANGELOG.md`](CHANGELOG.md)

## Requirements

- PHP `>= 8.1`
- Magento 2 (`magento/framework`, `magento/module-graph-ql`,
  `magento/module-store`, `magento/module-config`)
- The `flat_wheel_data` table already present in the Magento database

## Installation

Copy this directory into `app/code/Klever/VehicleTyresGuide/` of the target
Magento install, then from the Magento root:

```bash
bin/magento module:enable Klever_VehicleTyresGuide
bin/magento setup:upgrade
bin/magento setup:di:compile          # production mode only
bin/magento cache:clean config full_page
```

There is **no** `db_schema.xml` and **no** setup script — `setup:upgrade` will
not touch `flat_wheel_data`. Disabling the module likewise leaves the table
intact:

```bash
bin/magento module:disable Klever_VehicleTyresGuide
bin/magento setup:upgrade
bin/magento cache:clean
```

## Configuration

Admin path: **Stores → Configuration → Klever → Vehicle Tyres Guide**

Settings support default/website/store scope, so each website can have its own
`default_region` and `enabled` value.

| Field          | Config path                          | Scope                | Notes |
| -------------- | ------------------------------------ | -------------------- | ----- |
| Module Version | (read-only, from `composer.json`)    | Default              | House-standard version display |
| Enabled        | `klever_vehicle/general/enabled`     | Default/Website/Store | When off, `kleverVehicleMakes` throws an input error |
| Default Region | `klever_vehicle/general/default_region` | Default/Website/Store | e.g. `ae`, `sa`, `gb`, `us`; empty = all regions |
| Cache TTL      | `klever_vehicle/general/cache_ttl`   | Default              | Seconds; default `86400`, `0` disables caching |

## GraphQL queries

All type names are prefixed `KleverVehicle`; all query names `kleverVehicle`.

| Query | Purpose |
| ----- | ------- |
| `kleverVehicleMakes(region, ordering, limit, offset)` | List makes |
| `kleverVehicleModels(make!, region, ordering, limit, offset)` | List models for a make |
| `kleverVehicleYears(make!, model!)` | Production years for a make + model |
| `kleverVehicleModifications(make!, model!, year!, region)` | Full tyre/wheel/engine/fitment specs |
| `kleverVehicleGenerations(make!, model!)` | Generation buckets from start/end year |
| `kleverVehicleSearch(width!, height!, rim!)` | Vehicles matching a tyre size |

Example:

```graphql
query {
  kleverVehicleModifications(make: "toyota", model: "camry", year: 2020) {
    data {
      slug name trim start_year end_year is_stock
      engine { fuel capacity type power_hp power_kw drive code }
      front_wheel { rim_diameter rim_width tire_full tire_width tire_aspect_ratio pressure_psi }
      rear_wheel  { rim_diameter tire_full tire_width tire_aspect_ratio }
      fitment     { bolt_pattern pcd stud_holes centre_bore thread_size tightening_torque }
    }
    meta { count fuels hp_min hp_max }
  }
}

query {
  kleverVehicleSearch(width: 245, height: 60, rim: 18) {
    status message
    data { make_name model_name year_ranges engine_code front_width front_height front_rim }
  }
}
```

See the top-level [build prompt](../magento-module-prompt.md) for the complete
schema and additional sample queries.

## Architecture notes

- **All SQL lives in `Model/ResourceModel/FlatWheelData.php`.** Resolvers contain
  no raw SQL. Queries run through `Magento\Framework\App\ResourceConnection` with
  bound parameters; `LIMIT`/`OFFSET` are inlined as `(int)` casts (MySQL prepared
  statements reject placeholders there).
- **No Magento ORM.** No `AbstractModel`/`Collection` for `flat_wheel_data`, by design.
- **Read-only.** The module never issues writes against `flat_wheel_data`.
- **Caching.** Each query result is cached as JSON under the `klever_vehicle` cache
  tag for `cache_ttl` seconds. Flushing Magento cache (or `cache:clean`) clears it —
  run it after re-importing `flat_wheel_data`.
- **Multi-instance safe.** The module carries no data; each Magento instance imports
  its own copy of `flat_wheel_data` independently.

## Owned database tables

None created or managed. Reads (only) from the externally-managed `flat_wheel_data`.
