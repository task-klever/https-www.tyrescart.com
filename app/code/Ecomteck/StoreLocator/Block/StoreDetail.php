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

use Magento\Framework\View\Element\Template\Context;
use Ecomteck\StoreLocator\Model\Stores;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory as StoreLocatorCollectionFactory;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Store\Model\ScopeInterface;
use Ecomteck\StoreLocator\Model\Config as StoreLocatorConfig;
use Magento\Framework\Registry;
class StoreDetail extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry.
     *
     * @var Registry
     */
    private $coreRegistry;
    
    /**
     * Configuration
     *
     * @var StoreLocatorConfig
     */
    public $storelocatorConfig;
    
    public function __construct(
        StoreLocatorConfig $storelocatorConfig,
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->storelocatorConfig = $storelocatorConfig;
        $this->coreRegistry         = $coreRegistry;
        parent::__construct($context, $data);
    }

    public function getConfig()
    {
        $modulePath = "ecomteck_storelocator/stores/image/";
        $storeId = $this->getStoreId();
        if($this->getMapPin()){
            $mapPin = $this->getMediaUrl()."ecomteck_storelocator/".$this->getMapPin();
        } else {
            $mapPin = $this->getViewFileUrl('Ecomteck_StoreLocator::images/map_pin.png');
        }
        $config = [
            "map_styles"  => $this->storelocatorConfig->getMapStyles(),
            "map_pin" => $mapPin,
            "slider_arrow" => $this->getViewFileUrl('Ecomteck_StoreLocator::images/slider-arrow.svg'),
            "geolocation" => $this->storelocatorConfig->getLocationSettings(),
            "otherStores" => $this->storelocatorConfig->getOtherStoresSettings(),
            "otherStoresSlider" => $this->storelocatorConfig->getSliderSettings(),
            "modulePath" => $this->storelocatorConfig->getBaseImageUrl().$modulePath,
            "zoom_individual" => $this->storelocatorConfig->getZoomIndividualSettings(),
            "storeDetails" => $this->getStore()->getData(),
            "radius" => $this->storelocatorConfig->getRadiusSettings(),
            "apiKey" => $this->storelocatorConfig->getApiKeySettings(),
            "unit" => $this->storelocatorConfig->getUnitOfLengthSettings(),
        ];
        
        return $config;
    }

    public function getJsonConfig()
    {
        return json_encode($this->getConfig());
    }
    
    /**
     * return storelocator collection 
     *
     * @return CollectionFactory
     */
    public function getAllStoresCollection()
    {
        $collection = $this->storelocatorCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', Stores::STATUS_ENABLED)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('name', 'ASC');
        return $collection;
    }

    public function getStore()
    {
        return $this->coreRegistry->registry('current_store');
    }
}