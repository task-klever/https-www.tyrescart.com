<?php

declare(strict_types=1);

namespace Klever\ElasticTyreSearch\Model;

use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class TyreSizeEnrichmentService
{
    private ?string $vehicleImgDir = null;
    private array   $vehicleImgCache = [];

    public function __construct(
        private readonly FlatWheelData       $flatWheelData,
        private readonly ProductStatsProvider $productStats
    ) {
    }

    /**
     * Enrich an array of tyre size strings with vehicle, staggered, and product data.
     *
     * @param string[] $sizes e.g. ["205/55 R16", "195/65 R15"]
     * @return array keyed by size string
     */
    public function enrich(array $sizes): array
    {
        $result = [];
        foreach ($sizes as $size) {
            $parsed = $this->parseTyreSize($size);
            if (!$parsed) {
                $result[$size] = [];
                continue;
            }

            $w = $parsed['width'];
            $h = $parsed['height'];
            $r = $parsed['rim'];

            $vehicleStats   = $this->flatWheelData->getVehicleStatsForSize($w, $h, $r);
            $staggeredData  = $this->flatWheelData->getStaggeredPairings($w, $h, $r);
            $productData    = $this->productStats->getStatsForTyreSize($size);

            // Add vehicle images to sample_vehicles
            if (!empty($vehicleStats['sample_vehicles'])) {
                foreach ($vehicleStats['sample_vehicles'] as &$vehicle) {
                    $vehicle['image'] = $this->getVehicleImage(
                        $vehicle['make_slug'] ?? '',
                        $vehicle['model_slug'] ?? ''
                    );
                }
                unset($vehicle);
            }

            $result[$size] = array_merge(
                $vehicleStats,
                ['staggered_pairs' => $staggeredData['pairs'] ?? []],
                $productData
            );
        }
        return $result;
    }

    /**
     * Find a vehicle model image from /pub/media/vehicles/models/
     * Returns relative media path or empty string.
     */
    private function getVehicleImage(string $makeSlug, string $modelSlug): string
    {
        if (!$makeSlug || !$modelSlug) {
            return '';
        }

        $key = $makeSlug . '-' . $modelSlug;
        if (isset($this->vehicleImgCache[$key])) {
            return $this->vehicleImgCache[$key];
        }

        if ($this->vehicleImgDir === null) {
            $this->vehicleImgDir = BP . '/pub/media/vehicles/models/';
        }

        if (!$this->vehicleImgDir) {
            return $this->vehicleImgCache[$key] = '';
        }

        $pattern = $this->vehicleImgDir . $key . '-*.*';
        $matches = glob($pattern);
        if ($matches) {
            $filename = basename($matches[0]);
            return $this->vehicleImgCache[$key] = 'vehicles/models/' . $filename;
        }

        return $this->vehicleImgCache[$key] = '';
    }

    /**
     * Parse "205/55 R16" → ['width' => 205, 'height' => 55, 'rim' => 16]
     */
    private function parseTyreSize(string $size): ?array
    {
        if (preg_match('/(\d+)\s*\/\s*(\d+)\s*R\s*(\d+)/i', $size, $m)) {
            return [
                'width'  => (int) $m[1],
                'height' => (int) $m[2],
                'rim'    => (int) $m[3],
            ];
        }
        return null;
    }
}
