<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;
use Ecomteck\StoreLocator\Model\Config as StoreLocatorConfig;
use Magento\Framework\UrlInterface;
class DataProvider implements ConfigProviderInterface
{
    const XPATH_MAPS_API_KEY    = 'ecomteck_storepickup/general/maps_api_key';
    const XPATH_DEFAULT_LATITUDE    = 'ecomteck_storepickup/general/default_latitude';
    const XPATH_DEFAULT_LONGITUDE   = 'ecomteck_storepickup/general/default_longitude';
    const XPATH_DEFAULT_ZOOM        = 'ecomteck_storepickup/general/default_zoom';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ecomteck\StorePickup\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Configuration
     *
     * @var StoreLocatorConfig
     */
    public $storelocatorConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory $storeCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        StoreLocatorConfig $storelocatorConfig,
        UrlInterface $urlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storelocatorConfig = $storelocatorConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $store = $this->getStoreId();
        $mapsApiKey = $this->scopeConfig->getValue(self::XPATH_MAPS_API_KEY, ScopeInterface::SCOPE_STORE, $store);
        $defaultLatitude = $this->scopeConfig->getValue(self::XPATH_DEFAULT_LATITUDE, ScopeInterface::SCOPE_STORE, $store);
        $defaultLongitude = $this->scopeConfig->getValue(self::XPATH_DEFAULT_LONGITUDE, ScopeInterface::SCOPE_STORE, $store);
        $defaultZoom = $this->scopeConfig->getValue(self::XPATH_DEFAULT_ZOOM, ScopeInterface::SCOPE_STORE, $store);

        $config = [
            'shipping' => [
                'select_store' => [
                    'maps_api_key'   => $mapsApiKey,
                    'lat'   => (float)$defaultLatitude,
                    'lng'   => (float)$defaultLongitude,
                    'zoom'  => (int)$defaultZoom,
                    'stores' => $this->getStores()
                ],
                'storelocator' => $this->getStoreLocatorConfig()
            ]
        ];

        return $config;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    public function getStores()
    {
        $stores = $this->storeCollectionFactory
            ->create()
            ->addActiveFilter()
            ->toArray();
        //return \Zend_Json::encode($stores);
        return \Laminas\Json\Json::encode($stores);
        
    }

    public function getStoreLocatorConfig()
    {
        $config = [
            "apiKey" => $this->storelocatorConfig->getApiKeySettings(),
            "moduleUrl" => $this->storelocatorConfig->getModuleUrlSettings() ,
            // map settings
            "dataLocation" => $this->urlBuilder->getUrl('storelocator/ajax/stores',['quote'=>1]),
            "defaultLat" => $this->storelocatorConfig->getLatitudeSettings(),
            'defaultLng' => $this->storelocatorConfig->getLongitudeSettings(),
            "defaultLoc" => $this->storelocatorConfig->getLatitudeSettings() && $this->storelocatorConfig->getLongitudeSettings(),
            "loading" => false,
            "nameSearch" => $this->storelocatorConfig->getNameSearchSettings(),
            "openNearest" => $this->storelocatorConfig->getOpenNearestSettings(),
            "autoGeocode" => $this->storelocatorConfig->getLocationSettings(),
            "autoComplete" => $this->storelocatorConfig->getAddressAutoCompleteSettings(),
            "distanceAlert" => $this->storelocatorConfig->getDistanceAlertSettings(),
            "lengthUnit" => $this->storelocatorConfig->getUnitOfLengthSettings(),
            "pagination" => $this->storelocatorConfig->getPaginationSettings(),
            "locationsPerPage" => $this->storelocatorConfig->getLocationsPerPageSettings(),
            "storeLimit" => $this->storelocatorConfig->getStoreLimitSettings(),
            "maxDistance" => $this->storelocatorConfig->getMaxDistanceSettings(),
            "fullMapStart" => true,
            "slideMap" => false,
            "mapSettings"  => [
                "mapTypeId" => $this->storelocatorConfig->getMapTypeSettings(),
                "zoom"     => $this->storelocatorConfig->getZoomSettings()
            ],
            "inlineDirections" => $this->storelocatorConfig->getInlineDirectionsSettings(),
            // template settings
            'infowindowTemplatePath' => $this->urlBuilder->getUrl('storelocator/js/template',['_query' => ['template'=>'infowindow_description']]),
            'listTemplatePath'       =>    $this->urlBuilder->getUrl('storelocator/js/template',['_query' => ['template'=>'location_list_description']]),
            'KMLinfowindowTemplatePath' =>  $this->urlBuilder->getUrl('storelocator/js/template',['_query' => ['template'=>'kml_infowindow_description']]),
            'KMLlistTemplatePath'      => $this->urlBuilder->getUrl('storelocator/js/template',['_query' => ['template'=>'kml_location_list_description']]),
            // text settings
            "addressErrorAlert"          => $this->storelocatorConfig->getTextSettings('address_error_alert'),
            "autoGeocodeErrorAlert"      => $this->storelocatorConfig->getTextSettings('auto_geocode_error_alert'),
            "distanceErrorAlert"         => $this->storelocatorConfig->getTextSettings('distance_error_alert'),
            "kilometerLang"              => $this->storelocatorConfig->getTextSettings('kilometer_lang'),
            "kilometersLang"             => $this->storelocatorConfig->getTextSettings('kilometers_lang'),
            "mileLang"                   => $this->storelocatorConfig->getTextSettings('mile_lang'),
            "milesLang"                  => $this->storelocatorConfig->getTextSettings('miles_lang'),
            "noResultsTitle"             => $this->storelocatorConfig->getTextSettings('no_results_title'),
            "noResultsDesc"              => $this->storelocatorConfig->getTextSettings('no_results_desc'),
            "nextPage"                   => $this->storelocatorConfig->getTextSettings('next_page'),
            "prevPage"                   => $this->storelocatorConfig->getTextSettings('prev_page')
        ];
        if($this->storelocatorConfig->getMapPin()){
            $config["markerImg"] = $this->storelocatorConfig->getMapPin();
        }
        if($this->storelocatorConfig->getSelectedMapPin()){
            $config["selectedMarkerImg"] = $this->storelocatorConfig->getSelectedMapPin();
        }
        
        $config["mapSettings"]["styles"] = $this->storelocatorConfig->getMapStyle();
        
        
        return $config;
    }
}