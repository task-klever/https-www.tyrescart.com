<?php
namespace Hdweb\Tyrefinder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
// use Magento\Framework\HTTP\Client\Curl;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Data extends AbstractHelper
{
    // protected $curl;
    protected $flatWheelData;
    protected $_suggestedSizeCache = [];

    public function __construct(
        // Curl $curl
        FlatWheelData $flatWheelData
    ) {
        // $this->curl = $curl;
        $this->flatWheelData = $flatWheelData;
    }

    /**
     * Check if suggested size exists
     *
     * @param string $width
     * @param string $height
     * @param string $rim
     * @return bool
     */
    public function hasSuggestedSize($width, $height, $rim)
    {
        if (!$width || !$height || !$rim) {
            return false;
        }

        $cacheKey = $width . '_' . $height . '_' . $rim;
        if (array_key_exists($cacheKey, $this->_suggestedSizeCache)) {
            return $this->_suggestedSizeCache[$cacheKey];
        }

        try {
            // Old: External API call to Klever wheel-api
            // $apiKey = "49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9";
            // $url = "https://wheel-api.klever.ae/search.php?api_key={$apiKey}&width={$width}&height={$height}&rim={$rim}";
            // $this->curl->get($url);
            // $response = $this->curl->getBody();
            // $json = json_decode($response, true);
            // $result = false;
            // if (isset($json['status']) && $json['status'] === 'success') {
            //     if (!empty($json['data']) && is_array($json['data'])) {
            //         $result = count($json['data']) > 0;
            //     }
            // }

            // New: Read from Klever VehicleTyresGuide flat_wheel_data table
            $searchResult = $this->flatWheelData->searchByTyreSizeStaggered((int)$width, (int)$height, (int)$rim);
            $result = !empty($searchResult['rows']);

            $this->_suggestedSizeCache[$cacheKey] = $result;
            return $result;
        } catch (\Exception $e) {
            $this->_suggestedSizeCache[$cacheKey] = false;
            return false;
        }
    }

}
