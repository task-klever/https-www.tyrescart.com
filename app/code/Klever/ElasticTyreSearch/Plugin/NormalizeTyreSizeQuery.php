<?php
namespace Klever\ElasticTyreSearch\Plugin;

use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;

/**
 * Normalises tyre-size search queries so that formats without slashes/spaces
 * (e.g. "19565", "19565r15") return the same results as "195/65" / "195/65 R15".
 *
 * Patterns handled:
 *   19565       → 195/65
 *   19565r15    → 195/65 R15
 *   1956515     → 195/65 R15  (7 digits, no R)
 *   195/65r15   → 195/65 R15  (slash present but missing space before R)
 *   195/65 15   → 195/65 R15  (slash present, space, but no R before rim)
 *   195/6515    → 195/65 R15  (slash present, no space/R before rim)
 */
class NormalizeTyreSizeQuery
{
    /**
     * After-plugin on QueryFactory::get().
     * Rewrites the query text on the Query object once, so all downstream
     * consumers (ElasticSuite, autocomplete, collection filter) get the
     * normalised value without requiring a full DI recompile.
     *
     * @param QueryFactory $subject
     * @param Query        $result
     * @return Query
     */
    public function afterGet(QueryFactory $subject, Query $result): Query
    {
        $normalized = self::normalize($result->getQueryText());
        if ($normalized !== $result->getQueryText()) {
            $result->setQueryText($normalized);
        }
        return $result;
    }

    /**
     * Normalize a raw search string to a canonical tyre-size format.
     * Non-tyre-size strings are returned unchanged.
     *
     * @param string $query
     * @return string
     */
    public static function normalize(string $query): string
    {
        $q = trim($query);

        // "19565R15" or "19565r15" → "195/65 R15"
        if (preg_match('/^(\d{3})(\d{2})\s*[rR](\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2] . ' R' . $m[3];
        }

        // "1956515" → "195/65 R15"  (7 digits, no R separator)
        if (preg_match('/^(\d{3})(\d{2})(\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2] . ' R' . $m[3];
        }

        // "195/65R15" or "195/65r15" (missing space before R) → "195/65 R15"
        if (preg_match('/^(\d{3})\/(\d{2})\s*[rR](\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2] . ' R' . $m[3];
        }

        // "195/65 15" (space but no R before rim) → "195/65 R15"
        if (preg_match('/^(\d{3})\/(\d{2})\s+(\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2] . ' R' . $m[3];
        }

        // "195/6515" (slash, no space/R before rim) → "195/65 R15"
        if (preg_match('/^(\d{3})\/(\d{2})(\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2] . ' R' . $m[3];
        }

        // "195 65 15", "195 65 r15" → "195/65 R15"
        if (preg_match('/^(\d{3})\s+(\d{2})\s+[rR]?(\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2] . ' R' . $m[3];
        }

        // "195 65" → "195/65"
        if (preg_match('/^(\d{3})\s+(\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2];
        }

        // "19565" → "195/65"
        if (preg_match('/^(\d{3})(\d{2})\s*$/', $q, $m)) {
            return $m[1] . '/' . $m[2];
        }

        return $query;
    }
}
