<?php

namespace Hdweb\Vehicles\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\View\Asset\Repository as AssetRepository;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $scopeConfig;
    protected $_vehiclesCollection;
    protected $file;
    protected $moduleDirReader;
    protected $logger;
    protected $flatWheelData;
    protected $assetRepo;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Hdweb\Vehicles\Model\ResourceModel\Vehicles\Collection $vehiclesCollection,
        File $file,
        Reader $moduleDirReader,
        FlatWheelData $flatWheelData,
        AssetRepository $assetRepo
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
        $this->_vehiclesCollection = $vehiclesCollection;
        $this->file = $file;
        $this->moduleDirReader = $moduleDirReader;
        $this->flatWheelData = $flatWheelData;
        $this->assetRepo = $assetRepo;
        $this->logger = new MonologLogger('api_logger');
        $this->logger->pushHandler(new StreamHandler(BP . '/var/log/wheel-size-api-access.log', MonologLogger::INFO));
        parent::__construct($context);
    }


    public function logApiAccess($endpoint)
    {
        $this->logger->info("API accessed: $endpoint");
    }


    public function getVehiclesSearchbyFormUrl()
    {
        $vehiclesSearchbyFormUrl = $this->_storeManager->getStore()->getBaseUrl() . 'tyres.html?';
        return $vehiclesSearchbyFormUrl;
    }

    public function getVehicleDataFromMake($make)
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $this->_vehiclesCollection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $this->_vehiclesCollection->addFieldToFilter('make', ['eq' => $make]);
        // $this->_vehiclesCollection->addFieldToFilter('model', ['null' => true]);
        $this->_vehiclesCollection->addFieldToFilter('model', [['null' => true], ['eq' => ''],]);
        $vehicleData = $this->_vehiclesCollection->getFirstItem();
        return $vehicleData->getData();
    }

    public function getVehicleDataFromMakeModel($make, $model)
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $this->_vehiclesCollection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $this->_vehiclesCollection->addFieldToFilter('make', ['eq' => $make]);
        $this->_vehiclesCollection->addFieldToFilter('model', ['eq' => $model]);
        $vehicleData = $this->_vehiclesCollection->getFirstItem();

        return $vehicleData->getData();
    }

    public function getVehiclesMakes()
    {
        // Old: Read from local JSON file
        // $moduleDir = $this->moduleDirReader->getModuleDir('', 'Hdweb_Tyrefinder');
        // $filePath = $moduleDir . '/view/frontend/web/data/makes.json';
        // $jsonData = $this->file->read($filePath);
        // $vehicleMakes = json_decode($jsonData, true);
        // return $vehicleMakes;

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getMakes(['ordering' => 'name']);
        $rows = $result['rows'];
        foreach ($rows as &$row) {
            $row['logo'] = $this->assetRepo->getUrl("Hdweb_Vehicles::images/logo/" . $row['slug'] . ".png");
        }
        unset($row);
        return ['data' => $rows];
    }

    public function getVehicleModels($make)
    {
        // Old: External API call to Klever/wheel-size
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $vehicleModelUrl = "https://wheel-api.klever.ae/v1/models/?user_key=" . $wheelApiKey . "&make=" . $make . "&region=medm";
        // $vehicleModels = file_get_contents($vehicleModelUrl);
        // if(empty($vehicleModels)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $vehicleModelUrl = "https://api.wheel-size.com/v2/models/?user_key=" . $wheelApiKey . "&make=" . $make . "&region=medm";
        //     $this->logApiAccess($vehicleModelUrl);
        //     $vehicleModels = file_get_contents($vehicleModelUrl);
        // }
        // $vehicleModels = json_decode($vehicleModels);
        // return $vehicleModels;

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getModels($make, ['ordering' => 'name']);
        // Convert to object format to match template usage ($modelOption->slug, $modelOption->name)
        $data = [];
        foreach ($result['rows'] as $row) {
            $data[] = (object) ['slug' => $row['slug'], 'name' => $row['name']];
        }
        return (object) ['data' => $data];
    }

    public function getVehicleYears($make, $model)
    {
        // Old: External API call to Klever/wheel-size
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $modelyear_url = "https://wheel-api.klever.ae/v1/years/?user_key=" . $wheelApiKey . "&make=" . $make . "&model=" . $model . "&region=medm";
        // $modelYears = file_get_contents($modelyear_url);
        // if(empty($modelYears)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $modelyear_url = "https://api.wheel-size.com/v2/years/?user_key=" . $wheelApiKey . "&make=" . $make . "&model=" . $model . "&region=medm";
        //     $this->logApiAccess($modelyear_url);
        //     $modelYears = file_get_contents($modelyear_url);
        // }
        // $modelYears = json_decode($modelYears);
        // return $modelYears;

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getYears($make, $model);
        // Convert to object format to match template usage ($modelYear->slug, $modelYear->name)
        $data = [];
        foreach ($result['years'] as $year) {
            $data[] = (object) ['slug' => $year, 'name' => $year];
        }
        return (object) ['data' => $data];
    }

    public function getCarImageFromMakeModel($make, $model)
    {
        // Old: External API call to Klever/wheel-size for car image
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $generation_url = "https://wheel-api.klever.ae/v1/generations/?user_key=" . $wheelApiKey . "&make=" . $make . "&model=" . $model . "&region=medm";
        // $generation = file_get_contents($generation_url);
        // if(empty($generation)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $generation_url = "https://api.wheel-size.com/v2/generations/?user_key=" . $wheelApiKey . "&make=" . $make . "&model=" . $model . "&region=medm";
        //     $this->logApiAccess($generation_url);
        //     $generation = file_get_contents($generation_url);
        // }
        // $generation = json_decode($generation);
        // if (isset($generation->data[0]->bodies[0]->image)) {
        //     $carImage = $generation->data[0]->bodies[0]->image;
        // } else {
        //     $carImage = '';
        // }
        // return $carImage;

        // New: flat_wheel_data does not store car images — return make logo as fallback
        return $this->assetRepo->getUrl("Hdweb_Vehicles::images/logo/" . $make . ".png");
    }

    public function getMediaUrl()
    {
        $mediaURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaURL;
    }

    public function getMakeParagraph1Default()
    {
        $makeParagraph1Default = $this->scopeConfig->getValue('vehicles/general/make_paragraph1_default', ScopeInterface::SCOPE_STORE);
        return $makeParagraph1Default;
    }

    public function getMakeParagraph2Default()
    {
        $makeParagraph2Default = $this->scopeConfig->getValue('vehicles/general/make_paragraph2_default', ScopeInterface::SCOPE_STORE);
        return $makeParagraph2Default;
    }

    public function getModelParagraph1Default()
    {
        $modelParagraph1Default = $this->scopeConfig->getValue('vehicles/general/model_paragraph1_default', ScopeInterface::SCOPE_STORE);
        return $modelParagraph1Default;
    }

    public function getModelParagraph2Default()
    {
        $modelParagraph2Default = $this->scopeConfig->getValue('vehicles/general/model_paragraph2_default', ScopeInterface::SCOPE_STORE);
        return $modelParagraph2Default;
    }

    public function getDefaultMetaTitleForMakePage()
    {
        $makeDefaultMetaTitle = $this->scopeConfig->getValue('vehicles/general/make_default_meta_title', ScopeInterface::SCOPE_STORE);
        return $makeDefaultMetaTitle;
    }

    public function getDefaultMetaKeywordsForMakePage()
    {
        $makeDefaultMetaKeywords = $this->scopeConfig->getValue('vehicles/general/make_default_meta_keywords', ScopeInterface::SCOPE_STORE);
        return $makeDefaultMetaKeywords;
    }

    public function getDefaultMetaDescriptionForMakePage()
    {
        $makeDefaultMetaDescription = $this->scopeConfig->getValue('vehicles/general/make_default_meta_description', ScopeInterface::SCOPE_STORE);
        return $makeDefaultMetaDescription;
    }

    public function getDefaultMetaTitleForModelPage()
    {
        $modelDefaultMetaTitle = $this->scopeConfig->getValue('vehicles/general/model_default_meta_title', ScopeInterface::SCOPE_STORE);
        return $modelDefaultMetaTitle;
    }

    public function getDefaultMetaKeywordsForModelPage()
    {
        $modelDefaultMetaKeywords = $this->scopeConfig->getValue('vehicles/general/model_default_meta_keywords', ScopeInterface::SCOPE_STORE);
        return $modelDefaultMetaKeywords;
    }

    public function getDefaultMetaDescriptionForModelPage()
    {
        $modelDefaultMetaDescription = $this->scopeConfig->getValue('vehicles/general/model_default_meta_description', ScopeInterface::SCOPE_STORE);
        return $modelDefaultMetaDescription;
    }

    public function getH1TagForMakePage()
    {
        $h1Tag = $this->scopeConfig->getValue('vehicles/general/make_h1', ScopeInterface::SCOPE_STORE);
        return $h1Tag;
    }

    public function getH1TagForModelPage()
    {
        $h1Tag = $this->scopeConfig->getValue('vehicles/general/model_h1', ScopeInterface::SCOPE_STORE);
        return $h1Tag;
    }
}
