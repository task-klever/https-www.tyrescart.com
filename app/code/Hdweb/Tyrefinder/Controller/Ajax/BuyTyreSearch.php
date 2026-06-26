<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;
// use Magento\Framework\Filesystem\DirectoryList;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class BuyTyreSearch extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    // protected $scopeConfig;
    protected $storeManager;
    protected $assetRepo;
    // protected $directoryList;
    protected $flatWheelData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        // ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Repository $assetRepo,
        // DirectoryList $directoryList
        FlatWheelData $flatWheelData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        // $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        // $this->directoryList = $directoryList;
        $this->flatWheelData = $flatWheelData;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = ["status" => "error"];

        try {
            $width = $this->getRequest()->getParam('width');
            $height = $this->getRequest()->getParam('height');
            $rim = $this->getRequest()->getParam('rim');

            if (empty($width) || empty($height) || empty($rim)) {
                throw new \Exception('Required parameters are missing');
            }

            // Old: File-based cache + external API call
            // $cachedData = $this->getCachedResponse($width, $height, $rim);
            // if ($cachedData !== false) {
            //     $response = ["status" => "success", "html" => $this->generateHtml($cachedData)];
            //     return $this->resultJsonFactory->create()->setData($response);
            // }
            // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
            // $byTyreSearchUrl = "https://wheel-api.klever.ae/v1/by_tire/search/?section_width=" . $width .
            //                    "&aspect_ratio=" . $height . "&rim_diameter=" . $rim .
            //                    "&region=medm&user_key=" . $wheelApiKey;
            // $byTyreSearchResult = @file_get_contents($byTyreSearchUrl);
            // if(empty($byTyreSearchResult)) {
            //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
            //     $byTyreSearchUrl = "https://api.wheel-size.com/v2/by_tire/search/?section_width=" . $width .
            //                        "&aspect_ratio=" . $height . "&rim_diameter=" . $rim .
            //                        "&region=medm&user_key=" . $wheelApiKey;
            //     $byTyreSearchResult = @file_get_contents($byTyreSearchUrl);
            // }
            // $byTyreSearchResultArray = json_decode($byTyreSearchResult);
            // $this->cacheResponse($width, $height, $rim, $byTyreSearchResultArray->data);

            // New: Read from Klever VehicleTyresGuide flat_wheel_data table (already cached by FlatWheelData)
            $searchResult = $this->flatWheelData->searchByTyreSize((int)$width, (int)$height, (int)$rim);
            $data = $searchResult['rows'];

            if (empty($data)) {
                $response = [
                    "status" => "success",
                    "html" => '<div class="flex flex-col items-center justify-center py-8 text-center">'
                        . '<svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                        . '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'
                        . '</svg>'
                        . '<p class="text-base font-semibold text-gray-700">No matching vehicles found</p>'
                        . '<p class="text-sm text-gray-500 mt-1">We couldn\'t find any vehicles for this tyre size. Please contact us for assistance.</p>'
                        . '</div>'
                ];
                return $this->resultJsonFactory->create()->setData($response);
            }

            $response = ["status" => "success", "html" => $this->generateHtml($data)];

        } catch (\Exception $e) {
            $response = ["status" => "error", "message" => $e->getMessage()];
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    // Old: File-based caching methods (no longer needed - FlatWheelData handles caching)
    // protected function getCachedResponse($width, $height, $rim)
    // {
    //     $cacheDir = $this->directoryList->getPath('var') . '/buy_tyre_search/';
    //     $filename = $width . '_' . $height . '_' . $rim . '.json';
    //     $filePath = $cacheDir . $filename;
    //     if (file_exists($filePath)) {
    //         $data = json_decode(file_get_contents($filePath));
    //         return $data;
    //     }
    //     return false;
    // }

    // protected function cacheResponse($width, $height, $rim, $data)
    // {
    //     $cacheDir = $this->directoryList->getPath('var') . '/buy_tyre_search/';
    //     if (!file_exists($cacheDir)) {
    //         mkdir($cacheDir, 0777, true);
    //     }
    //     $filename = $width . '_' . $height . '_' . $rim . '.json';
    //     file_put_contents($cacheDir . $filename, json_encode($data));
    // }

    /**
     * Generate HTML from data
     */
    protected function generateHtml($data)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $resultCustomArray = [];

        foreach ($data as $singleData) {
            // Support both object (old) and array (new) format
            if (is_object($singleData)) {
                $makeSlug = $singleData->make->slug;
                $makeName = $singleData->make->name;
                $modelName = $singleData->name;
                $modelSlug = $singleData->slug;
                $yearRanges = $singleData->year_ranges;
            } else {
                $makeSlug = $singleData['make_slug'] ?? '';
                $makeName = $singleData['make_name'] ?? '';
                $modelName = $singleData['model_name'] ?? '';
                $modelSlug = $singleData['model_slug'] ?? '';
                $yearRanges = $singleData['year_ranges'] ?? '';
            }
            $resultCustomArray[$makeSlug][] = [
                'make_name' => $makeName,
                'make_slug' => $makeSlug,
                'model_name' => $modelName,
                'model_slug' => $modelSlug,
                'year_ranges' => $yearRanges,
            ];
        }

        $htmlResponse = '';
        foreach ($resultCustomArray as $makeSlug => $resultCustom) {
            $makeName = $resultCustom[0]['make_name'];

            $htmlResponse .= '<div class="flex justify-between items-start mb-3 pb-3 border-b border-dashed border-theme-blue gap-3">
                            <div class="modal-name-cotent" style="flex:0 0 auto;">
                                <div class="left-content">
                                <div class="brand-image flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 rounded-[6px] px-3 py-1 border border-blue-200 group-hover:border-blue-300 transition-colors">
                                    <img class="w-8 h-auto" src="' . $this->assetRepo->getUrl("Hdweb_Vehicles::images/logo/" . $makeSlug . ".png") . '" alt="' . $makeName . '" />
                                    <p class="ml-2 text-xs font-semibold uppercase text-gray-800 tracking-wide">' . $makeName . '</p>
                                </div>
                                </div>
                            </div>
                            <div class="modal-results-content">
                                <div class="brand-info-content flex gap-2 flex-wrap justify-end">';
            foreach ($resultCustom as $singleCustom) {
                $modelName = $singleCustom['model_name'];
                $modelSlug = $singleCustom['model_slug'];
                $yearRanges = $singleCustom['year_ranges'];

                $htmlResponse .= '<a class="brand-info" href="' . $baseUrl . 'tyres/cars/' . $makeSlug . '/' . $modelSlug . '">
                                <div class="model-year font-semibold text-gray-900 text-sm hover:text-theme-blue transition-colors text-center">
                                    <span>' . $modelName . '</span>';

                // year_ranges can be a JSON string from flat_wheel_data or an array
                if (is_string($yearRanges)) {
                    $decoded = json_decode($yearRanges, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $yearRange) {
                            $htmlResponse .= '<span class="text-xs text-gray-500 block">' . $yearRange . '</span>';
                        }
                    } else {
                        $htmlResponse .= '<span class="text-xs text-gray-500 block">' . $yearRanges . '</span>';
                    }
                } elseif (is_array($yearRanges)) {
                    foreach ($yearRanges as $yearRange) {
                        $htmlResponse .= '<span class="text-xs text-gray-500 block">' . $yearRange . '</span>';
                    }
                }

                $htmlResponse .= '</div>
                            </a>';
            }
            $htmlResponse .= '</div></div></div>';
        }

        return $htmlResponse;
    }
}
