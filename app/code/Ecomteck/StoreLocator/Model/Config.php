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
 
namespace Ecomteck\StoreLocator\Model;

use Magento\Backend\Block\Template\Context;
use Ecomteck\StoreLocator\Model\Stores;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory as StoreLocatorCollectionFactory;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

class Config
{
    /**
     * @var string
     */
    const MAP_STYLES_CONFIG_PATH = 'ecomteck_storelocator/map/map_style';

    /**
     * @var string
     */
    const CUSTOM_MAP_STYLES_CONFIG_PATH = 'ecomteck_storelocator/map/custom_map_style';

    /**
     * @var string
     */
    const URL_CONFIG_PATH = 'ecomteck_storelocator/seo/url';
            
    /**
     * @var string
     */
    const MAP_PIN_CONFIG_PATH = 'ecomteck_storelocator/map/map_pin';

    /**
     * @var string
     */
    const SELECTED_MAP_PIN_CONFIG_PATH = 'ecomteck_storelocator/map/selected_map_pin';
    
            
    /**
     * @var string
     */
    const ASK_LOCATION_CONFIG_PATH = 'ecomteck_storelocator/search/ask_location';

    /**
     * @var string
     */
    const DINSTANCE_STEPS_CONFIG_PATH = 'ecomteck_storelocator/search/distance_steps';
    
    /**
     * @var string
     */
    const TEMPLATE_CONFIG_PATH = 'ecomteck_storelocator/map/template';

    /**
     * @var string
     */
    const API_KEY_CONFIG_PATH = 'ecomteck_storelocator/map/api_key';

    /**
     * @var string
     */
    const MAP_TYPE_CONFIG_PATH = 'ecomteck_storelocator/map/map_type';
    

    /**
     * @var string
     */
    const UNIT_LENGTH_CONFIG_PATH = 'ecomteck_storelocator/map/unit_length';
        
    /**
     * @var int
     */
    const LATITUDE_CONFIG_PATH = 'ecomteck_storelocator/map/latitude';
            
    /**
     * @var int
     */
    const LONGITUDE_CONFIG_PATH = 'ecomteck_storelocator/map/longitude';
            
    /**
     * @var int
     */
    const ZOOM_CONFIG_PATH = 'ecomteck_storelocator/map/zoom';

    /**
     * @var int
     */
    const ZOOM_INDIVIDUAL_CONFIG_PATH = 'ecomteck_storelocator/individual/zoom_individual';

    /**
     * @var int
     */
    const SLIDER_CONFIG_PATH = 'ecomteck_storelocator/individual/stores_slider';

    /**
     * @var int
     */
    const SLIDER_STORE_LIMIT_CONFIG_PATH = 'ecomteck_storelocator/individual/slider_store_limit';

    /**
     * @var int
     */
    const OTHER_STORES_CONFIG_PATH = 'ecomteck_storelocator/individual/other_stores';

    /**
     * @var int
     */
    const LOCATIONS_PER_PAGE_CONFIG_PATH = 'ecomteck_storelocator/search/locations_per_page';
       
    /**
     * @var bool
     */
    const PAGINATION_CONFIG_PATH = 'ecomteck_storelocator/search/pagination';

    /**
     * @var bool
     */
    const OPEN_NEAREST_CONFIG_PATH = 'ecomteck_storelocator/search/open_nearest';

    /**
     * @var bool
     */
    const MAX_DISTANCE_CONFIG_PATH = 'ecomteck_storelocator/search/max_distance';

    /**
     * @var bool
     */
    const NAME_SEARCH_CONFIG_PATH = 'ecomteck_storelocator/search/name_search';


    /**
     * @var string
     */
    const RADIUS_CONFIG_PATH = 'ecomteck_storelocator/search/radius';

    /**
     * @var string
     */
    const STORE_LIMIT_CONFIG_PATH = 'ecomteck_storelocator/search/store_limit';
    

    /**
     * @var string
     */
    const INLINE_DIRECTIONS_CONFIG_PATH = 'ecomteck_storelocator/search/inline_directions';

    /**
     * @var float
     */
    const DISTANCE_ALERT_CONFIG_PATH = 'ecomteck_storelocator/search/distance_alert';
    

    /**
     * @var string
     */
    const ADDRESS_AUTO_COMPLETE_CONFIG_PATH = 'ecomteck_storelocator/search/address_autocomplete';

    /**
     * @var string
     */
    const TEXT_CONFIG_PATH = 'ecomteck_storelocator/text/%s';

    /**
     * @var string
     */
    const ALLOW_FILTER_CONFIG_PATH = 'ecomteck_storelocator/filter/allow_%s_filter';

    const FILTER_RENDERER_CONFIG_PATH = 'ecomteck_storelocator/filter/%s_filter_renderer';

    /**
     * @var string
     */
    const COUNTRIES_CONFIG_PATH = 'ecomteck_storelocator/filter/countries';
    
    /**
     * @var string
     */
    const CATEGORIES_CONFIG_PATH = 'ecomteck_storelocator/filter/categories';

    /**
     * @var string
     */
    const CITIES_CONFIG_PATH = 'ecomteck_storelocator/filter/cities';

    /**
     * @var StoreLocatorCollectionFactory
     */
    public $storelocatorCollectionFactory;
        
    /**
     * @var Country
     */
    public $countryHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    public function __construct(
        StoreLocatorCollectionFactory $storelocatorCollectionFactory,
        Country $countryHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->storelocatorCollectionFactory = $storelocatorCollectionFactory;
        $this->countryHelper = $countryHelper;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    
    /**
      * return storelocator collection
      *
      * @return CollectionFactory
      */
      public function getStoresForFrontend()
      {
          $storeLimit = $this->getSliderStoreLimitSettings();
          $collection = $this->storelocatorCollectionFactory->create()
              ->addFieldToSelect('*')
              ->addFieldToFilter('status', Stores::STATUS_ENABLED)
              ->setPageSize($storeLimit)
              ->setOrder('name', 'ASC');
          return $collection;
      }
  
      /**
       * Get store identifier
       *
       * @return  string
       */
      public function getStoreId()
      {
          return $this->_storeManager->getStore()->getId();
      }
      
      /**
       * get an array of country codes and country names: AF => Afganisthan
       *
       * @return array
       */
      public function getCountries()
      {
  
          $loadCountries = $this->countryHelper->toOptionArray();
          $countries = [];
          $i = 0;
          foreach ($loadCountries as $country ) {
              $i++;
              if ($i == 1) { //remove first element that is a select
                  continue;
              }
              $countries[$country["value"]] = $country["label"];
          }
          return $countries;
      }
      
      /**
       * get media url
       *
       * @return string
       */  
      public function getMediaUrl()
      {
          return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
      }
      
      /**
       * get map style from configuration
       *
       * @return string
       */   
      public function getMapStyles()
      {
        return $this->_scopeConfig->getValue(self::MAP_STYLES_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
      }

      /**
       * get map style from configuration
       *
       * @return string
       */   
      public function getCustomMapStyles()
      {
        return $this->_scopeConfig->getValue(self::CUSTOM_MAP_STYLES_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
      }

      /**
       * get map style from configuration
       *
       * @return string
       */   
      public function getMapStyle()
      {
          $style =  $this->getMapStyles();
          if($style == 'custom'){
            $customMapStyle = $this->getCustomMapStyles();
            $customMapStyle = json_decode($customMapStyle,true);
            return $customMapStyle;
          }
          $styles = '{
            "default": "",
            "ultra_light_with_labels": [
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#e9e9e9"
                    }, {
                        "lightness": 17
                    }]
                }, {
                    "featureType": "landscape",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f5f5f5"
                    }, {
                        "lightness": 20
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "geometry.fill",
                    "stylers": [{
                        "color": "#ffffff"
                    }, {
                        "lightness": 17
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "geometry.stroke",
                    "stylers": [{
                        "color": "#ffffff"
                    }, {
                        "lightness": 29
                    }, {
                        "weight": 0.2
                    }]
                }, {
                    "featureType": "road.arterial",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#ffffff"
                    }, {
                        "lightness": 18
                    }]
                }, {
                    "featureType": "road.local",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#ffffff"
                    }, {
                        "lightness": 16
                    }]
                }, {
                    "featureType": "poi",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f5f5f5"
                    }, {
                        "lightness": 21
                    }]
                }, {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#dedede"
                    }, {
                        "lightness": 21
                    }]
                }, {
                    "elementType": "labels.text.stroke",
                    "stylers": [{
                        "visibility": "on"
                    }, {
                        "color": "#ffffff"
                    }, {
                        "lightness": 16
                    }]
                }, {
                    "elementType": "labels.text.fill",
                    "stylers": [{
                        "saturation": 36
                    }, {
                        "color": "#333333"
                    }, {
                        "lightness": 40
                    }]
                }, {
                    "elementType": "labels.icon",
                    "stylers": [{
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "transit",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f2f2f2"
                    }, {
                        "lightness": 19
                    }]
                }, {
                    "featureType": "administrative",
                    "elementType": "geometry.fill",
                    "stylers": [{
                        "color": "#fefefe"
                    }, {
                        "lightness": 20
                    }]
                }, {
                    "featureType": "administrative",
                    "elementType": "geometry.stroke",
                    "stylers": [{
                        "color": "#fefefe"
                    }, {
                        "lightness": 17
                    }, {
                        "weight": 1.2
                    }]
                }
            ],
            "hopper": [
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#165c64"
                    }, {
                        "saturation": 34
                    }, {
                        "lightness": -69
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "landscape",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#b7caaa"
                    }, {
                        "saturation": -14
                    }, {
                        "lightness": -18
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "landscape.man_made",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#cbdac1"
                    }, {
                        "saturation": -6
                    }, {
                        "lightness": -9
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#8d9b83"
                    }, {
                        "saturation": -89
                    }, {
                        "lightness": -12
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#d4dad0"
                    }, {
                        "saturation": -88
                    }, {
                        "lightness": 54
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road.arterial",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#bdc5b6"
                    }, {
                        "saturation": -89
                    }, {
                        "lightness": -3
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road.local",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#bdc5b6"
                    }, {
                        "saturation": -89
                    }, {
                        "lightness": -26
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "poi",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#c17118"
                    }, {
                        "saturation": 61
                    }, {
                        "lightness": -45
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "poi.park",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#8ba975"
                    }, {
                        "saturation": -46
                    }, {
                        "lightness": -28
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "transit",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#a43218"
                    }, {
                        "saturation": 74
                    }, {
                        "lightness": -51
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "administrative.province",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#ffffff"
                    }, {
                        "saturation": 0
                    }, {
                        "lightness": 100
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "administrative.neighborhood",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#ffffff"
                    }, {
                        "saturation": 0
                    }, {
                        "lightness": 100
                    }, {
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "administrative.locality",
                    "elementType": "labels",
                    "stylers": [{
                        "hue": "#ffffff"
                    }, {
                        "saturation": 0
                    }, {
                        "lightness": 100
                    }, {
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "administrative.land_parcel",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#ffffff"
                    }, {
                        "saturation": 0
                    }, {
                        "lightness": 100
                    }, {
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "administrative",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#3a3935"
                    }, {
                        "saturation": 5
                    }, {
                        "lightness": -57
                    }, {
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "poi.medical",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#cba923"
                    }, {
                        "saturation": 50
                    }, {
                        "lightness": -46
                    }, {
                        "visibility": "on"
                    }]
                }
            ],
            "light_dream": [
                {
                    "featureType": "landscape",
                    "stylers": [{
                        "hue": "#FFBB00"
                    }, {
                        "saturation": 43.400000000000006
                    }, {
                        "lightness": 37.599999999999994
                    }, {
                        "gamma": 1
                    }]
                }, {
                    "featureType": "road.highway",
                    "stylers": [{
                        "hue": "#FFC200"
                    }, {
                        "saturation": -61.8
                    }, {
                        "lightness": 45.599999999999994
                    }, {
                        "gamma": 1
                    }]
                }, {
                    "featureType": "road.arterial",
                    "stylers": [{
                        "hue": "#FF0300"
                    }, {
                        "saturation": -100
                    }, {
                        "lightness": 51.19999999999999
                    }, {
                        "gamma": 1
                    }]
                }, {
                    "featureType": "road.local",
                    "stylers": [{
                        "hue": "#FF0300"
                    }, {
                        "saturation": -100
                    }, {
                        "lightness": 52
                    }, {
                        "gamma": 1
                    }]
                }, {
                    "featureType": "water",
                    "stylers": [{
                        "hue": "#0078FF"
                    }, {
                        "saturation": -13.200000000000003
                    }, {
                        "lightness": 2.4000000000000057
                    }, {
                        "gamma": 1
                    }]
                }, {
                    "featureType": "poi",
                    "stylers": [{
                        "hue": "#00FF6A"
                    }, {
                        "saturation": -1.0989010989011234
                    }, {
                        "lightness": 11.200000000000017
                    }, {
                        "gamma": 1
                    }]
                }
            ],
            "blue_water": [
                {
                    "featureType": "administrative",
                    "elementType": "labels.text.fill",
                    "stylers": [{
                        "color": "#444444"
                    }]
                }, {
                    "featureType": "landscape",
                    "elementType": "all",
                    "stylers": [{
                        "color": "#f2f2f2"
                    }]
                }, {
                    "featureType": "poi",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "road",
                    "elementType": "all",
                    "stylers": [{
                        "saturation": -100
                    }, {
                        "lightness": 45
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road.arterial",
                    "elementType": "labels.icon",
                    "stylers": [{
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "transit",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "water",
                    "elementType": "all",
                    "stylers": [{
                        "color": "#46bcec"
                    }, {
                        "visibility": "on"
                    }]
                }
            ],
            "pale_down": [
                {
                    "featureType": "administrative",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "on"
                    }, {
                        "lightness": 33
                    }]
                }, {
                    "featureType": "landscape",
                    "elementType": "all",
                    "stylers": [{
                        "color": "#f2e5d4"
                    }]
                }, {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#c5dac6"
                    }]
                }, {
                    "featureType": "poi.park",
                    "elementType": "labels",
                    "stylers": [{
                        "visibility": "on"
                    }, {
                        "lightness": 20
                    }]
                }, {
                    "featureType": "road",
                    "elementType": "all",
                    "stylers": [{
                        "lightness": 20
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#c5c6c6"
                    }]
                }, {
                    "featureType": "road.arterial",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#e4d7c6"
                    }]
                }, {
                    "featureType": "road.local",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#fbfaf7"
                    }]
                }, {
                    "featureType": "water",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "on"
                    }, {
                        "color": "#acbcc9"
                    }]
                }
            ],
            "paper": [
                {
                    "featureType": "administrative",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "landscape",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "simplified"
                    }, {
                        "hue": "#0066ff"
                    }, {
                        "saturation": 74
                    }, {
                        "lightness": 100
                    }]
                }, {
                    "featureType": "poi",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "off"
                    }, {
                        "weight": 0.6
                    }, {
                        "saturation": -85
                    }, {
                        "lightness": 61
                    }]
                }, {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [{
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "road.arterial",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "road.local",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "transit",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "water",
                    "elementType": "all",
                    "stylers": [{
                        "visibility": "simplified"
                    }, {
                        "color": "#5f94ff"
                    }, {
                        "lightness": 26
                    }, {
                        "gamma": 5.86
                    }]
                }
            ],
            "light_monochrome": [
                {
                    "featureType": "administrative.locality",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#2c2e33"
                    }, {
                        "saturation": 7
                    }, {
                        "lightness": 19
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "landscape",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#ffffff"
                    }, {
                        "saturation": -100
                    }, {
                        "lightness": 100
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "poi",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#ffffff"
                    }, {
                        "saturation": -100
                    }, {
                        "lightness": 100
                    }, {
                        "visibility": "off"
                    }]
                }, {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#bbc0c4"
                    }, {
                        "saturation": -93
                    }, {
                        "lightness": 31
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road",
                    "elementType": "labels",
                    "stylers": [{
                        "hue": "#bbc0c4"
                    }, {
                        "saturation": -93
                    }, {
                        "lightness": 31
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "road.arterial",
                    "elementType": "labels",
                    "stylers": [{
                        "hue": "#bbc0c4"
                    }, {
                        "saturation": -93
                    }, {
                        "lightness": -2
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "road.local",
                    "elementType": "geometry",
                    "stylers": [{
                        "hue": "#e9ebed"
                    }, {
                        "saturation": -90
                    }, {
                        "lightness": -8
                    }, {
                        "visibility": "simplified"
                    }]
                }, {
                    "featureType": "transit",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#e9ebed"
                    }, {
                        "saturation": 10
                    }, {
                        "lightness": 69
                    }, {
                        "visibility": "on"
                    }]
                }, {
                    "featureType": "water",
                    "elementType": "all",
                    "stylers": [{
                        "hue": "#e9ebed"
                    }, {
                        "saturation": -78
                    }, {
                        "lightness": 67
                    }, {
                        "visibility": "simplified"
                    }]
                }
            ]
        }';
          $styles = json_decode($styles,true);
          if(!empty($styles[$style])){
              return $styles[$style];
          }
          return '';
    }
          
    /**
     * get map pin from configuration
     *
     * @return string or null
     */   
    public function getMapPin()
    {
        $value = $this->_scopeConfig->getValue(self::MAP_PIN_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if($value){
            return $this->getMediaUrl().'ecomteck_storelocator/'.$value;
        }
        return '';
    }

    /**
     * get selected map pin from configuration
     *
     * @return string or null
     */   
    public function getSelectedMapPin()
    {
        $value = $this->_scopeConfig->getValue(self::SELECTED_MAP_PIN_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if($value){
            return $this->getMediaUrl().'ecomteck_storelocator/'.$value;
        }
        return '';
    }
    
    /**
     * get location settings from configuration
     *
     * @return int
     */   
    public function getLocationSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::ASK_LOCATION_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
        
    /**
     * get template settings from configuration, i.e full width or page width
     *
     * @return string
     */   
    public function getTemplateSettings()
    {
        return $this->_scopeConfig->getValue(self::TEMPLATE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get api key settings from configuration
     *
     * @return string
     */
    public function getApiKeySettings()
    {
        return $this->_scopeConfig->getValue(self::API_KEY_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
        
    /**
     * get unit of length settings from configuration
     *
     * @return string
     */   
    public function getUnitOfLengthSettings()
    {
        return $this->_scopeConfig->getValue(self::UNIT_LENGTH_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
            
    /**
     * get zoom settings from configuration
     *
     * @return int
     */   
    public function getZoomSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::ZOOM_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get url settings from configuration
     *
     * @return string
     */
    public function getModuleUrlSettings()
    {
        return $this->_scopeConfig->getValue(self::URL_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get slider settings from configuration
     *
     * @return int
     */
    public function getSliderSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::SLIDER_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get other stores settings from configuration
     *
     * @return int
     */
    public function getOtherStoresSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::OTHER_STORES_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get individual zoom settings from configuration for store details page
     *
     * @return int
     */
    public function getZoomIndividualSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::ZOOM_INDIVIDUAL_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get slider store limit settings from configuration for store details page
     *
     * @return int
     */
    public function getSliderStoreLimitSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::SLIDER_STORE_LIMIT_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get 
     *
     * @return int
     */
    public function getLocationsPerPageSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::LOCATIONS_PER_PAGE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get
     *
     * @return int
     */
    public function getStoreLimitSettings()
    {
        return (int)$this->_scopeConfig->getValue(self::STORE_LIMIT_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
    

    /**
     * get distance alert
     *
     * @return float
     */
    public function getDistanceAlertSettings()
    {
        return (float)$this->_scopeConfig->getValue(self::DISTANCE_ALERT_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get latitude settings from configuration
     *
     * @return float
     */   
    public function getLatitudeSettings()
    {
        return (float)$this->_scopeConfig->getValue(self::LATITUDE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
            
    /**
     * get longitude settings from configuration
     *
     * @return float
     */   
    public function getLongitudeSettings()
    {
        return (float)$this->_scopeConfig->getValue(self::LONGITUDE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
                
    /**
     * get radius settings from configuration
     *
     * @return float
     */   
    public function getRadiusSettings()
    {
        return (float)$this->_scopeConfig->getValue(self::RADIUS_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * get base image url
     *
     * @return string
     */ 
    public function getBaseImageUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * get map type
     *
     * @return string
     */ 
    public function getMapTypeSettings()
    {
        return (string)$this->_scopeConfig->getValue(self::MAP_TYPE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
    

    /**
     * get inline directions settings
     *
     * @return string
     */ 
    public function getInlineDirectionsSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::INLINE_DIRECTIONS_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get inline directions settings
     *
     * @return string
     */ 
    public function getPaginationSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::PAGINATION_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
    

    /**
     * get inline directions settings
     *
     * @return string
     */ 
    public function getAddressAutoCompleteSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::ADDRESS_AUTO_COMPLETE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get Open Nearest Settings
     *
     * @return string
     */ 
    public function getOpenNearestSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::OPEN_NEAREST_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
  
    /**
     * get Max Distance Settings
     *
     * @return string
     */ 
    public function getMaxDistanceSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::MAX_DISTANCE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * get Name Search Settings
     *
     * @return string
     */ 
    public function getNameSearchSettings()
    {
        return (bool)$this->_scopeConfig->getValue(self::NAME_SEARCH_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
  
    public function getTextSettings($text)
    {
        return (string)$this->_scopeConfig->getValue(sprintf(self::TEXT_CONFIG_PATH,$text), ScopeInterface::SCOPE_STORE);
    }

    public function getDinstanceSteps()
    {
        $dinstanceSteps =  $this->_scopeConfig->getValue(self::DINSTANCE_STEPS_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if($dinstanceSteps){
            $dinstanceSteps = json_decode($dinstanceSteps,true);
        }
        
        return $dinstanceSteps;
    }

    public function getCountriesSettings()
    {
        $allow = $this->_scopeConfig->getValue(sprintf(self::ALLOW_FILTER_CONFIG_PATH,'country'), ScopeInterface::SCOPE_STORE);
        if(!$allow){
            return [];
        }
        $config =  $this->_scopeConfig->getValue(self::COUNTRIES_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        $config = json_decode($config,true);
        $countries = [];
        foreach($config as $row){
            $countries[] = $row['country'];
        }
        return $countries;
    }

    public function getCategoriesSettings()
    {
        $allow = $this->_scopeConfig->getValue(sprintf(self::ALLOW_FILTER_CONFIG_PATH,'category'), ScopeInterface::SCOPE_STORE);
        if(!$allow){
            return [];
        }
        $config =  $this->_scopeConfig->getValue(self::CATEGORIES_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        $config = json_decode($config,true);
        $categories = [];
        foreach($config as $row){
            $categories[] = $row['category'];
        }
        return $categories;
    }

    public function getCitiesSettings()
    {
        $allow = $this->_scopeConfig->getValue(sprintf(self::ALLOW_FILTER_CONFIG_PATH,'city'), ScopeInterface::SCOPE_STORE);
        if(!$allow){
            return [];
        }
        $config =  $this->_scopeConfig->getValue(self::CITIES_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        $config = json_decode($config,true);
        $cities = [];
        foreach($config as $row){
            $cities[] = $row['city'];
        }
        return $cities;
    }

    public function getFilters()
    {
        $filters = [];
        $categories = $this->getCategoriesSettings();
        if(count($categories)){
            $renderer = $this->_scopeConfig->getValue(sprintf(self::FILTER_RENDERER_CONFIG_PATH,'category'), ScopeInterface::SCOPE_STORE);
            $filters['category'] = [
                'renderer' => $renderer,
                'label' => __('Category'),
                'items' => []
            ];
            $filters['category']['items'][] = ['value'=>'','label'=>__('All Categories')];
            foreach($categories as $category){
                $filters['category']['items'][] = ['value'=>$category,'label'=>$category];
            }
        }

        $countries = $this->getCountriesSettings();
        if(count($countries)){
            $renderer = $this->_scopeConfig->getValue(sprintf(self::FILTER_RENDERER_CONFIG_PATH,'country'), ScopeInterface::SCOPE_STORE);
            $filters['country'] = [
                'renderer' => $renderer,
                'label' => __('Country'),
                'items' => []
            ];
            $filters['country']['items'][] = ['value'=>'','label'=>__('All countries')];
            foreach($countries as $country){
                $filters['country']['items'][] = ['value'=>$country,'label'=>$country];
            }
        }   
        $cities = $this->getCitiesSettings();
        if(count($cities)){
            $renderer = $this->_scopeConfig->getValue(sprintf(self::FILTER_RENDERER_CONFIG_PATH,'city'), ScopeInterface::SCOPE_STORE);
            $filters['city'] = [
                'renderer' => $renderer,
                'label' => __('City'),
                'items' => []
            ];
            $filters['city']['items'][] = ['value'=>'','label'=>__('All cities')];
            foreach($cities as $city){
                $filters['city']['items'][] = ['value'=>$city,'label'=>$city];
            }
        }
        return $filters;
    }
}