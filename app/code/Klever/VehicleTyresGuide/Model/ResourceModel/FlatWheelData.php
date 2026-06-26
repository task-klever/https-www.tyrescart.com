<?php

declare(strict_types=1);

namespace Klever\VehicleTyresGuide\Model\ResourceModel;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;

class FlatWheelData
{
    private const TABLE     = 'flat_wheel_data';
    private const CACHE_TAG = 'klever_vehicle';

    public function __construct(
        private readonly ResourceConnection   $resource,
        private readonly CacheInterface       $cache,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    // ── Cache helpers ────────────────────────────────────────────────────────

    private function getCacheTtl(): int
    {
        return (int) ($this->scopeConfig->getValue(
            'klever_vehicle/general/cache_ttl',
            ScopeInterface::SCOPE_STORE
        ) ?? 86400);
    }

    private function cacheKey(string $method, array $params): string
    {
        return self::CACHE_TAG . '_' . $method . '_' . md5(serialize($params));
    }

    private function fromCache(string $key): ?array
    {
        $raw = $this->cache->load($key);
        return $raw ? json_decode($raw, true) : null;
    }

    private function toCache(string $key, array $data): void
    {
        $ttl = $this->getCacheTtl();
        if ($ttl > 0) {
            $this->cache->save(json_encode($data), $key, [self::CACHE_TAG], $ttl);
        }
    }

    // ── Connection ──────────────────────────────────────────────────────────

    private function connection()
    {
        return $this->resource->getConnection();
    }

    private function table(): string
    {
        return $this->resource->getTableName(self::TABLE);
    }

    // ── Year range helpers ───────────────────────────────────────────────────

    /**
     * Parse JSON year_ranges strings (e.g. '["2010-2015","2017"]') and
     * also include raw start_year / end_year integers from rows.
     * Returns unique integers sorted descending.
     */
    public function expandYears(array $rows): array
    {
        $years = [];
        foreach ($rows as $row) {
            // From year_ranges JSON
            if (!empty($row['year_ranges'])) {
                $decoded = json_decode((string)$row['year_ranges'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $entry) {
                        $entry = trim((string)$entry);
                        if (strpos($entry, '-') !== false) {
                            [$a, $b] = array_map('trim', explode('-', $entry, 2));
                            if (is_numeric($a) && is_numeric($b)) {
                                for ($y = (int)$a; $y <= (int)$b; $y++) {
                                    $years[] = $y;
                                }
                            }
                        } elseif (is_numeric($entry)) {
                            $years[] = (int)$entry;
                        }
                    }
                }
            }
            // From start_year / end_year columns
            if (!empty($row['start_year']) && !empty($row['end_year'])) {
                for ($y = (int)$row['start_year']; $y <= (int)$row['end_year']; $y++) {
                    $years[] = $y;
                }
            }
        }
        $years = array_values(array_unique($years));
        rsort($years, SORT_NUMERIC);
        return $years;
    }

    /**
     * Compact integer year array into range strings: [2010,2011,2012,2017] → ["2010-2012","2017"]
     */
    public function compactYearRanges(array $years): array
    {
        if (empty($years)) {
            return [];
        }
        sort($years, SORT_NUMERIC);
        $ranges = [];
        $start  = $years[0];
        $prev   = $years[0];
        for ($i = 1, $n = count($years); $i < $n; $i++) {
            if ($years[$i] === $prev + 1) {
                $prev = $years[$i];
            } else {
                $ranges[] = $start === $prev ? (string)$start : "$start-$prev";
                $start    = $years[$i];
                $prev     = $years[$i];
            }
        }
        $ranges[] = $start === $prev ? (string)$start : "$start-$prev";
        return $ranges;
    }

    // ── Enrichment queries ──────────────────────────────────────────────────

    /**
     * VEHICLE STATS FOR SIZE — how many models/makes fit this tyre size
     */
    public function getVehicleStatsForSize(int $width, int $height, int $rim): array
    {
        $cacheKey = $this->cacheKey('vstats', ['w' => $width, 'h' => $height, 'r' => $rim]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $where = "(front_tire_width = :w AND front_tire_aspect_ratio = :h AND front_rim_diameter = :r)
                  OR (rear_tire_width = :w2 AND rear_tire_aspect_ratio = :h2 AND rear_rim_diameter = :r2)";
        $bind  = [':w' => $width, ':h' => $height, ':r' => $rim,
                  ':w2' => $width, ':h2' => $height, ':r2' => $rim];

        $stats = $conn->fetchRow(
            "SELECT COUNT(DISTINCT make_slug, model_slug) AS model_count,
                    COUNT(DISTINCT make_slug) AS make_count
             FROM {$table}
             WHERE {$where}",
            $bind
        );

        /* $samples = $conn->fetchCol(
            "SELECT DISTINCT CONCAT(make_name, ' ', model_name) AS vehicle
             FROM {$table}
             WHERE {$where}
             ORDER BY make_name ASC
             LIMIT 5",
            $bind
        ); */

        $sampleRows = $conn->fetchAll(
            "SELECT DISTINCT make_name, model_name, make_slug, model_slug
             FROM {$table}
             WHERE {$where}
             ORDER BY make_name ASC",
            $bind
        );

        $samples = [];
        foreach ($sampleRows as $row) {
            $samples[] = [
                'name'       => $row['make_name'] . ' ' . $row['model_name'],
                'make_slug'  => $row['make_slug'],
                'model_slug' => $row['model_slug'],
            ];
        }

        $result = [
            'model_count'     => (int) ($stats['model_count'] ?? 0),
            'make_count'      => (int) ($stats['make_count'] ?? 0),
            'sample_vehicles' => $samples,
        ];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * STAGGERED PAIRINGS — find front/rear size combinations for a given tyre size
     */
    public function getStaggeredPairings(int $width, int $height, int $rim): array
    {
        $cacheKey = $this->cacheKey('stagger', ['w' => $width, 'h' => $height, 'r' => $rim]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        // This size as front → find different rear sizes
        $rearPairs = $conn->fetchAll(
            "SELECT rear_tire_width AS pair_width,
                    rear_tire_aspect_ratio AS pair_height,
                    rear_rim_diameter AS pair_rim,
                    COUNT(*) AS frequency
             FROM {$table}
             WHERE front_tire_width = :w AND front_tire_aspect_ratio = :h AND front_rim_diameter = :r
               AND rear_tire_width IS NOT NULL
               AND (rear_tire_width != front_tire_width
                    OR rear_tire_aspect_ratio != front_tire_aspect_ratio
                    OR rear_rim_diameter != front_rim_diameter)
             GROUP BY rear_tire_width, rear_tire_aspect_ratio, rear_rim_diameter
             ORDER BY frequency DESC
             LIMIT 5",
            [':w' => $width, ':h' => $height, ':r' => $rim]
        );

        // This size as rear → find different front sizes
        $frontPairs = $conn->fetchAll(
            "SELECT front_tire_width AS pair_width,
                    front_tire_aspect_ratio AS pair_height,
                    front_rim_diameter AS pair_rim,
                    COUNT(*) AS frequency
             FROM {$table}
             WHERE rear_tire_width = :w AND rear_tire_aspect_ratio = :h AND rear_rim_diameter = :r
               AND (front_tire_width != rear_tire_width
                    OR front_tire_aspect_ratio != rear_tire_aspect_ratio
                    OR front_rim_diameter != rear_rim_diameter)
             GROUP BY front_tire_width, front_tire_aspect_ratio, front_rim_diameter
             ORDER BY frequency DESC
             LIMIT 5",
            [':w' => $width, ':h' => $height, ':r' => $rim]
        );

        $pairs = [];
        foreach ($rearPairs as $row) {
            $pw = (int)$row['pair_width'];
            $ph = (int)$row['pair_height'];
            $pr = (int)$row['pair_rim'];
            $pairs[] = [
                'size'        => $pw . '/' . $ph . ' R' . $pr,
                'position'    => 'rear',
                'frequency'   => (int)$row['frequency'],
                'front_width' => $width, 'front_height' => $height, 'front_rim' => $rim,
                'rear_width'  => $pw,    'rear_height'  => $ph,     'rear_rim'  => $pr,
            ];
        }
        foreach ($frontPairs as $row) {
            $pw = (int)$row['pair_width'];
            $ph = (int)$row['pair_height'];
            $pr = (int)$row['pair_rim'];
            $pairs[] = [
                'size'        => $pw . '/' . $ph . ' R' . $pr,
                'position'    => 'front',
                'frequency'   => (int)$row['frequency'],
                'front_width' => $pw,    'front_height' => $ph,     'front_rim' => $pr,
                'rear_width'  => $width, 'rear_height'  => $height, 'rear_rim'  => $rim,
            ];
        }

        // Sort by frequency descending, keep top 5 overall
        usort($pairs, fn($a, $b) => $b['frequency'] <=> $a['frequency']);
        $pairs = array_slice($pairs, 0, 5);

        // Fetch vehicles for each pairing
        foreach ($pairs as &$pair) {
            $vehicles = $conn->fetchAll(
                "SELECT DISTINCT make_name, model_name, make_slug, model_slug
                 FROM {$table}
                 WHERE front_tire_width = :fw AND front_tire_aspect_ratio = :fh AND front_rim_diameter = :fr
                   AND rear_tire_width = :rw AND rear_tire_aspect_ratio = :rh AND rear_rim_diameter = :rr
                 ORDER BY make_name ASC
                 LIMIT 10",
                [
                    ':fw' => $pair['front_width'], ':fh' => $pair['front_height'], ':fr' => $pair['front_rim'],
                    ':rw' => $pair['rear_width'],  ':rh' => $pair['rear_height'],  ':rr' => $pair['rear_rim'],
                ]
            );
            $pair['vehicles'] = [];
            foreach ($vehicles as $v) {
                $pair['vehicles'][] = [
                    'name'       => $v['make_name'] . ' ' . $v['model_name'],
                    'make_slug'  => $v['make_slug'],
                    'model_slug' => $v['model_slug'],
                ];
            }
        }
        unset($pair);

        $result = ['pairs' => $pairs];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    // ── Queries ──────────────────────────────────────────────────────────────

    /**
     * MAKES — distinct makes from flat table
     * Params: region (string|null), ordering (string), limit (int|null), offset (int)
     */
    public function getMakes(array $params): array
    {
        $cacheKey = $this->cacheKey('makes', $params);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $ordering = match ($params['ordering'] ?? 'slug') {
            'name'  => 'make_name ASC',
            '-name' => 'make_name DESC',
            '-slug' => 'make_slug DESC',
            default => 'make_slug ASC',
        };

        $where = ["make_slug IS NOT NULL", "make_slug != ''"];
        $bind  = [];

        // Count
        $countSql = "SELECT COUNT(DISTINCT make_slug) FROM {$table} WHERE " . implode(' AND ', $where);
        $total    = (int)$conn->fetchOne($countSql, $bind);

        // Data
        $sql = "SELECT DISTINCT make_slug AS slug, make_name AS name
                FROM {$table}
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$ordering}";

        if (!empty($params['limit'])) {
            $sql .= ' LIMIT ' . (int)$params['limit'] . ' OFFSET ' . (int)($params['offset'] ?? 0);
        }

        $rows   = $conn->fetchAll($sql, $bind);
        $result = ['rows' => $rows, 'total' => $total];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * MODELS — distinct models for a make
     * Params: make (string), region (string|null), ordering (string),
     *         limit (int|null), offset (int)
     */
    public function getModels(string $makeSlug, array $params): array
    {
        $cacheKey = $this->cacheKey('models', array_merge(['make' => $makeSlug], $params));
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $ordering = match ($params['ordering'] ?? 'slug') {
            'name'  => 'model_name ASC',
            '-name' => 'model_name DESC',
            '-slug' => 'model_slug DESC',
            default => 'model_slug ASC',
        };

        $where = ["make_slug = :make", "model_slug IS NOT NULL", "model_slug != ''"];
        $bind  = [':make' => $makeSlug];

        $countSql = "SELECT COUNT(DISTINCT model_slug) FROM {$table} WHERE " . implode(' AND ', $where);
        $total    = (int)$conn->fetchOne($countSql, $bind);

        $sql = "SELECT
                    model_slug AS slug,
                    model_name AS name,
                    GROUP_CONCAT(DISTINCT year_ranges ORDER BY year_ranges SEPARATOR '||') AS all_year_ranges
                FROM {$table}
                WHERE " . implode(' AND ', $where) . "
                GROUP BY model_slug, model_name
                ORDER BY {$ordering}";

        if (!empty($params['limit'])) {
            $sql .= ' LIMIT ' . (int)$params['limit'] . ' OFFSET ' . (int)($params['offset'] ?? 0);
        }

        $rows = $conn->fetchAll($sql, $bind);

        // Decode and merge year_ranges per model
        foreach ($rows as &$row) {
            $merged = [];
            foreach (explode('||', (string)$row['all_year_ranges']) as $json) {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $entry) {
                        $merged[] = (string)$entry;
                    }
                }
            }
            $row['year_ranges'] = array_values(array_unique($merged));
            unset($row['all_year_ranges']);
        }
        unset($row);

        $result = ['rows' => $rows, 'total' => $total];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * YEARS — all production years for a make + model
     */
    public function getYears(string $makeSlug, string $modelSlug): array
    {
        $cacheKey = $this->cacheKey('years', ['make' => $makeSlug, 'model' => $modelSlug]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $rows = $conn->fetchAll(
            "SELECT DISTINCT year_ranges, start_year, end_year
             FROM {$table}
             WHERE make_slug = :make AND model_slug = :model",
            [':make' => $makeSlug, ':model' => $modelSlug]
        );

        $years  = $this->expandYears($rows);
        $result = ['years' => $years];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * MODIFICATIONS — full specs for make + model + year
     * Matches rows where :year falls within start_year..end_year
     * Falls back to JSON year_ranges if start/end year columns are null
     */
    public function getModifications(string $makeSlug, string $modelSlug, int $year): array
    {
        $cacheKey = $this->cacheKey('mods', [
            'make' => $makeSlug, 'model' => $modelSlug, 'year' => $year
        ]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $rows = $conn->fetchAll(
            "SELECT * FROM {$table}
             WHERE make_slug = :make
               AND model_slug = :model
               AND JSON_CONTAINS(available_years, CAST(:year AS JSON))
               AND JSON_CONTAINS(regions, '\"medm\"')",
            [':make' => $makeSlug, ':model' => $modelSlug, ':year' => $year]
        );

        $result = ['rows' => $rows];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * GENERATIONS — distinct generation groups derived from start_year / end_year
     */
    public function getGenerations(string $makeSlug, string $modelSlug): array
    {
        $cacheKey = $this->cacheKey('gens', ['make' => $makeSlug, 'model' => $modelSlug]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $rows = $conn->fetchAll(
            "SELECT DISTINCT
                 modification_slug,
                 modification_name,
                 start_year,
                 end_year
             FROM {$table}
             WHERE make_slug = :make
               AND model_slug = :model
               AND modification_slug IS NOT NULL
               AND modification_slug != ''
               AND start_year IS NOT NULL
               AND end_year IS NOT NULL
             ORDER BY start_year DESC",
            [':make' => $makeSlug, ':model' => $modelSlug]
        );

        // Group by (start_year, end_year) to form generation buckets
        $generations = [];
        foreach ($rows as $row) {
            $key = $row['start_year'] . '_' . $row['end_year'];
            if (!isset($generations[$key])) {
                $start = (int)$row['start_year'];
                $end   = (int)$row['end_year'];
                $years = range($start, $end);

                $generations[$key] = [
                    'slug'        => $makeSlug . '-' . $modelSlug . '-' . $start . '-' . $end,
                    'name'        => 'Generation ' . $start . '-' . $end,
                    'start_year'  => $start,
                    'end_year'    => $end,
                    'years'       => $years,
                    'year_ranges' => $this->compactYearRanges($years),
                ];
            }
        }

        $result = ['rows' => array_values($generations)];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * TYRE SIZES — all distinct tyre sizes (front + rear combined)
     * Returns: [['width' => ..., 'height' => ..., 'rim' => ...], ...]
     */
    public function getTyreSizes(): array
    {
        $cacheKey = $this->cacheKey('tyre_sizes', []);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $rows = $conn->fetchAll(
            "SELECT DISTINCT width, height, rim FROM (
                 SELECT front_tire_width AS width,
                        front_tire_aspect_ratio AS height,
                        front_rim_diameter AS rim
                 FROM {$table}
                 WHERE front_tire_width IS NOT NULL
                   AND front_tire_aspect_ratio IS NOT NULL
                   AND front_rim_diameter IS NOT NULL
                 UNION
                 SELECT rear_tire_width AS width,
                        rear_tire_aspect_ratio AS height,
                        rear_rim_diameter AS rim
                 FROM {$table}
                 WHERE rear_tire_width IS NOT NULL
                   AND rear_tire_aspect_ratio IS NOT NULL
                   AND rear_rim_diameter IS NOT NULL
             ) AS sizes
             ORDER BY width ASC, height ASC, rim ASC"
        );

        $result = ['rows' => $rows];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * SEARCH BY TYRE SIZE — match front or rear tyre dimensions
     * width = tyre width (mm), height = aspect ratio, rim = rim diameter (inches)
     */
    public function searchByTyreSize(int $width, int $height, int $rim): array
    {
        $cacheKey = $this->cacheKey('search', [
            'width' => $width, 'height' => $height, 'rim' => $rim
        ]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $rows = $conn->fetchAll(
            "SELECT
                 MIN(t.front_width)  AS front_width,
                 MIN(t.front_height) AS front_height,
                 MIN(t.front_rim)    AS front_rim,
                 MIN(t.rear_width)   AS rear_width,
                 MIN(t.rear_height)  AS rear_height,
                 MIN(t.rear_rim)     AS rear_rim,
                 MAX(t.is_stock)     AS is_stock,
                 t.make_name, t.make_slug,
                 t.model_name, t.model_slug,
                 t.year_ranges,
                 GROUP_CONCAT(DISTINCT t.engine_code ORDER BY t.engine_code SEPARATOR ', ') AS engine_code
             FROM (
                 SELECT
                     front_tire_width AS front_width,
                     front_tire_aspect_ratio AS front_height,
                     front_rim_diameter AS front_rim,
                     COALESCE(rear_tire_width, front_tire_width) AS rear_width,
                     COALESCE(rear_tire_aspect_ratio, front_tire_aspect_ratio) AS rear_height,
                     COALESCE(rear_rim_diameter, front_rim_diameter) AS rear_rim,
                     is_stock, make_name, make_slug, model_name, model_slug, year_ranges, engine_code
                 FROM {$table}
                 WHERE front_tire_width = :fw1
                   AND front_tire_aspect_ratio = :fh1
                   AND front_rim_diameter = :fr1
                 UNION ALL
                 SELECT
                     front_tire_width AS front_width,
                     front_tire_aspect_ratio AS front_height,
                     front_rim_diameter AS front_rim,
                     COALESCE(rear_tire_width, front_tire_width) AS rear_width,
                     COALESCE(rear_tire_aspect_ratio, front_tire_aspect_ratio) AS rear_height,
                     COALESCE(rear_rim_diameter, front_rim_diameter) AS rear_rim,
                     is_stock, make_name, make_slug, model_name, model_slug, year_ranges, engine_code
                 FROM {$table}
                 WHERE rear_tire_width = :rw1
                   AND rear_tire_aspect_ratio = :rh1
                   AND rear_rim_diameter = :rr1
             ) AS t
             GROUP BY
                 t.make_slug, t.model_slug, t.year_ranges",
            [
                ':fw1' => $width, ':fh1' => $height, ':fr1' => $rim,
                ':rw1' => $width, ':rh1' => $height, ':rr1' => $rim,
            ]
        );

        $result = ['rows' => $rows];
        $this->toCache($cacheKey, $result);
        return $result;
    }

    /**
     * SEARCH BY TYRE SIZE (staggered only) — only vehicles with explicit different front/rear sizes
     */
    public function searchByTyreSizeStaggered(int $width, int $height, int $rim): array
    {
        $cacheKey = $this->cacheKey('search_staggered', [
            'width' => $width, 'height' => $height, 'rim' => $rim
        ]);
        if ($cached = $this->fromCache($cacheKey)) {
            return $cached;
        }

        $conn  = $this->connection();
        $table = $this->table();

        $rows = $conn->fetchAll(
            "SELECT
                 front_tire_width        AS front_width,
                 front_tire_aspect_ratio AS front_height,
                 front_rim_diameter      AS front_rim,
                 rear_tire_width         AS rear_width,
                 rear_tire_aspect_ratio  AS rear_height,
                 rear_rim_diameter       AS rear_rim,
                 is_stock,
                 make_name, make_slug,
                 model_name, model_slug,
                 year_ranges,
                 engine_code
             FROM {$table}
             WHERE
                 front_tire_width        IS NOT NULL AND front_tire_width        != ''
                 AND front_tire_aspect_ratio IS NOT NULL AND front_tire_aspect_ratio != ''
                 AND front_rim_diameter      IS NOT NULL AND front_rim_diameter      != ''
                 AND rear_tire_width         IS NOT NULL AND rear_tire_width         != ''
                 AND rear_tire_aspect_ratio  IS NOT NULL AND rear_tire_aspect_ratio  != ''
                 AND rear_rim_diameter       IS NOT NULL AND rear_rim_diameter       != ''
                 AND (
                     (front_tire_width = :fw AND front_tire_aspect_ratio = :fh AND front_rim_diameter = :fr)
                     OR
                     (rear_tire_width  = :rw AND rear_tire_aspect_ratio  = :rh AND rear_rim_diameter  = :rr)
                 )
             GROUP BY
                 front_tire_width, front_tire_aspect_ratio, front_rim_diameter,
                 rear_tire_width, rear_tire_aspect_ratio, rear_rim_diameter,
                 is_stock, make_name, make_slug, model_name, model_slug, year_ranges, engine_code",
            [
                ':fw' => $width,  ':fh' => $height, ':fr' => $rim,
                ':rw' => $width,  ':rh' => $height, ':rr' => $rim,
            ]
        );

        $result = ['rows' => $rows];
        $this->toCache($cacheKey, $result);
        return $result;
    }
}
