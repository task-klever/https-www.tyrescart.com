<?php

namespace Hdweb\Vehicles\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getsearchbymodel extends \Magento\Framework\App\Action\Action
{
    protected $_resource;
    protected $resultJsonFactory;
    protected $vehiclesHelper;
    protected $config;
    // protected $scopeConfig;
    protected $flatWheelData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Hdweb\Vehicles\Helper\Data $vehiclesHelper,
        \Magento\Eav\Model\Config $config,
        // ScopeConfigInterface $scopeConfig
        FlatWheelData $flatWheelData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resource         = $resource;
        $this->vehiclesHelper    = $vehiclesHelper;
        $this->config            = $config;
        // $this->scopeConfig       = $scopeConfig;
        $this->flatWheelData     = $flatWheelData;
        parent::__construct($context);
    }

    public function execute()
    {
        $widthattributeCode = 'width';
        $widthattribute     = $this->config->getAttribute('catalog_product', $widthattributeCode);
        $widhtOptions       = $widthattribute->getSource()->getAllOptions();

        $heightattributeCode = 'height';
        $heightattribute     = $this->config->getAttribute('catalog_product', $heightattributeCode);
        $heightOptions       = $heightattribute->getSource()->getAllOptions();

        $rimtattributeCode = 'rim';
        $rimattribute      = $this->config->getAttribute('catalog_product', $rimtattributeCode);
        $rimOptions        = $rimattribute->getSource()->getAllOptions();

        $response  = [];
        $make      = $this->getRequest()->getParam('make');
        $model     = $this->getRequest()->getParam('model');
        $year      = $this->getRequest()->getParam('year');
        $modification  = $this->getRequest()->getParam('modification');

        // Old: External API call to Klever/wheel-size
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $searchby_model_url = "https://wheel-api.klever.ae/v1/search/by_model/?user_key=" . $wheelApiKey . "&make=" . $make . "&model=" . $model . "&year=" . $year . "&modification=" . $modification;
        // $modelengine = file_get_contents($searchby_model_url);
        // if(empty($modelengine)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $searchby_model_url = "https://api.wheel-size.com/v2/search/by_model/?user_key=" . $wheelApiKey . "&make=" . $make . "&model=" . $model . "&year=" . $year . "&modification=" . $modification;
        //     $this->vehiclesHelper->logApiAccess($searchby_model_url);
        //     $modelengine = file_get_contents($searchby_model_url);
        // }
        // $modelengine = json_decode($modelengine);

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getModifications($make, $model, (int)$year);
        $modificationsData = $result['rows'];

        // Filter by modification slug if provided
        if (!empty($modification)) {
            $modificationsData = array_filter($modificationsData, function($row) use ($modification) {
                return ($row['modification_slug'] ?? '') === $modification;
            });
            $modificationsData = array_values($modificationsData);
        }

        $Engines    = [];
        $engineHtml = "";

        foreach ($modificationsData as $key => $row) {
            $trim = [];
            $name                  = ($row['start_year'] ?? '') . '-' . ($row['end_year'] ?? '');
            $slug                  = $row['modification_slug'] ?? '';
            $trim['name']          = $row['trim'] ?? '';
            $trim['power']         = $row['power_hp'] ?? '';
            $Engines[$name][$slug] = $trim;
        }

        foreach ($Engines as $key => $country) {
            $engineHtml .= "<li class='col-12 d-none'><span class='block-title'>" . $key . "<span></li>";
            foreach ($country as $slugkey => $slugvalue) {
                $engineHtml .= '<li class="col"><a href="javascript:void(0)" class="button button-block button-primary-100 button-rounded filled-slide-right button-text-overflow" onclick="getTyreSizes(\'' . $slugkey . '\',\'' . $slugvalue['name'] . '\')" ><span>' . $slugvalue['name'] . '<sup class="lightsup" title="248hp | 185kW | 252PS">' . $slugvalue['power'] . 'hp</sup></span></a><span id="autosearch-span" style="display:none">' . $slugvalue['name'] . ' ' . $slugvalue['power'] . 'hp</span></li>';
            }
        }

        $enginesTyre      = [];
        $enginesTyreArray = [];
        $alltyresize      = [];

        foreach ($modificationsData as $key => $row) {
            $slug = $row['modification_slug'] ?? '';

            $frontTireWidth = $row['front_tire_width'] ?? null;
            $frontTireAspectRatio = $row['front_tire_aspect_ratio'] ?? null;
            $frontRimDiameter = $row['front_rim_diameter'] ?? null;
            $rearTireWidth = $row['rear_tire_width'] ?? null;
            $rearTireAspectRatio = $row['rear_tire_aspect_ratio'] ?? null;
            $rearRimDiameter = $row['rear_rim_diameter'] ?? null;
            $frontTire = $row['front_tire'] ?? null;

            if (empty($frontTireWidth)) {
                continue;
            }

            $fronttire = str_replace('Z', '', $frontTire ?? '');
            $dupeKey = $fronttire . '|' . ($row['rear_tire'] ?? '');
            if (in_array($dupeKey, $alltyresize)) {
                continue;
            }
            $alltyresize[] = $dupeKey;

            $oemClass  = '';
            $oemLabel  = '';

            if (isset($row['is_stock']) && (bool)$row['is_stock']) {
                $oemClass = 'oem';
                $oemLabel = '<span class="oem-label absolute top-[-12px] right-0 oe-text"><span class="inline-block px-1.5 py-[2px] rounded text-[0.6rem] font-bold text-white bg-theme-blue">OE SIZE</span></span>';
            }

            // format strings with space before R
            $frontFormatted = $frontTireWidth . '/' . $frontTireAspectRatio . ' R' . $frontRimDiameter;
            $rearFormatted  = $rearTireWidth ? $rearTireWidth . '/' . $rearTireAspectRatio . ' R' . $rearRimDiameter : '';

            // with rear
            if ($rearTireWidth) {
                $tyreKey = $frontFormatted . ' ' . $rearFormatted;
                $enginesTyre[$tyreKey] = '<a class="group relative rounded-[10px] cursor-pointer border transition-all duration-200 min-w-[107px] md:min-w-[116px] border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm text-center px-4 py-3">
                                                <div class="' . $oemClass . ' ' . $slug . '">
                                                    <span class="block text-sm font-medium [direction:ltr]"
                                                        title="' . $tyreKey . '"
                                                        onclick="vehiclesShowProduct(\'' . $frontTireWidth . '\',\'' . $frontTireAspectRatio . '\',\'' . $frontRimDiameter . '\',\'' . $rearTireWidth . '\',\'' . $rearTireAspectRatio . '\',\'' . $rearRimDiameter . '\')">
                                                        ' . $oemLabel . '
                                                        <span>' . $tyreKey . '</span>
                                                    </span>
                                                    <span id="autosearch-span" style="display:none">' . $tyreKey . '</span>
                                                </div>
                                          </a>';
            } else {
                // only front
                $tyreKey = $frontFormatted;
                $enginesTyre[$tyreKey] = '<div class="group relative rounded-[10px] cursor-pointer border transition-all duration-200 min-w-[107px] md:min-w-[116px] border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm text-center px-4 py-3">
                    <div class="' . $oemClass . ' ' . $slug . '">
                        <span class="block text-sm font-medium [direction:ltr]"
                            title="' . $tyreKey . '"
                            onclick="vehiclesShowProduct(\'' . $frontTireWidth . '\',\'' . $frontTireAspectRatio . '\',\'' . $frontRimDiameter . '\')">
                            ' . $oemLabel . '
                            <span>' . $tyreKey . '</span>
                        </span>
                        <span id="autosearch-span" style="display:none">' . $tyreKey . '</span>
                    </div>
                </div>';
            }
        }

        foreach ($enginesTyre as $key => $enginesTyreValues) {
            $enginesTyreArray[] = $enginesTyreValues;
        }

        $response['engineHtml']  = $engineHtml;
        $response['enginesTyre'] = $enginesTyreArray;

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}










