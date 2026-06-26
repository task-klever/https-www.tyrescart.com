<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
 
namespace Ecomteck\StoreLocator\Block;

use Magento\Catalog\Block\Product\Context;
use Ecomteck\StoreLocator\Model\Stores;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory as StoreLocatorCollectionFactory;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Store\Model\ScopeInterface;
use Ecomteck\StoreLocator\Model\Config as StoreLocatorConfig;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;
class StoreLocator extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Configuration
     *
     * @var StoreLocatorConfig
     */
    public $storelocatorConfig;

    protected $_pageConfig;

    protected $storescollection;
    
    public function __construct(
        StoreLocatorConfig $storelocatorConfig,
        CollectionFactory $collectionFactory,
        Context $context,
        \Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection $storescollection,
        array $data = []
    ) {
        $this->storelocatorConfig = $storelocatorConfig;
        $this->_coreRegistry = $context->getRegistry();
        $this->collectionFactory = $collectionFactory;
        $this->_pageConfig = $context->getPageConfig();
        $this->storescollection        = $storescollection;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $templateSetting = $this->storelocatorConfig->getTemplateSettings();
        $template = 'Ecomteck_StoreLocator::storelocator/right.phtml';
        switch($templateSetting) {
            case 'full_width_right_sidebar' :
                $this->setIsFullWidth(true);
                break;
            case 'full_width_left_sidebar' :
                $template = 'Ecomteck_StoreLocator::storelocator/left.phtml';
                $this->setIsFullWidth(true);
                break;
            case 'page_right_sidebar' :
                $template = 'Ecomteck_StoreLocator::storelocator/right.phtml';
                break;
            case 'page_left_sidebar' :
                $template = 'Ecomteck_StoreLocator::storelocator/left.phtml';
                break;
        }
        $this->setTemplate($template);
        return parent::_construct();
    }

    protected function _prepareLayout()
    {
        if($this->getIsFullWidth()){
            $this->_pageConfig->addBodyClass('full-width');
        }
        return parent::_prepareLayout();
    }
    
    public function getConfig()
    {
        $config = [
            "apiKey" => $this->storelocatorConfig->getApiKeySettings(),
            "moduleUrl" => $this->storelocatorConfig->getModuleUrlSettings() ,
            // map settings
            "dataLocation" => $this->getUrl('storelocator/ajax/stores'),
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
            "mapSettings"  => [
                "mapTypeId" => $this->storelocatorConfig->getMapTypeSettings(),
                "zoom"     => $this->storelocatorConfig->getZoomSettings()
            ],
            "inlineDirections" => $this->storelocatorConfig->getInlineDirectionsSettings(),
            // template settings
            'infowindowTemplatePath' => $this->getUrl('storelocator/js/template',['_query' => ['template'=>'infowindow_description']]),
            'listTemplatePath'       =>    $this->getUrl('storelocator/js/template',['_query' => ['template'=>'location_list_description']]),
            'KMLinfowindowTemplatePath' =>  $this->getUrl('storelocator/js/template',['_query' => ['template'=>'kml_infowindow_description']]),
            'KMLlistTemplatePath'      => $this->getUrl('storelocator/js/template',['_query' => ['template'=>'kml_location_list_description']]),
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
        $filters = $this->getFilters();
        if(!empty($filters)){
            $config['taxonomyFilters'] = [];
            foreach($filters as $name => $filter){
                $config['taxonomyFilters'][$name] = $name.'-filters-container';
            }
        }
        
        return $config;
    }

    public function getJsonConfig()
    {
        return json_encode($this->getConfig());
    }

    public function getFilters()
    {
        return $this->storelocatorConfig->getFilters(); 
    }

    public function getStorePickupList()
    {
        $stores = $this->storescollection->addFieldToFilter('status', 1);
        return $stores;
    }
}
