<?php
namespace Hdweb\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Data extends AbstractHelper
{
    protected $scopeConfig;

    protected $productFactory;

    protected $productCollectionFactory;

    protected $flatWheelData;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
        CollectionFactory $productCollectionFactory,
        FlatWheelData $flatWheelData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->flatWheelData = $flatWheelData;
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getRelatedItems(string $linkType, $productId){
        $product = $this->productFactory->create();
        $product->load($productId);
        $partsCategory = $product->getAttributeText('parts_category');
        $collection = [];
        
        if($partsCategory == 'Tyres'){
            $width = $product->getAttributeText('width');
            $height = $product->getAttributeText('height');
            $rim = $product->getAttributeText('rim');

            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('entity_id', ['nin' => $productId]);
            $collection->addFieldToFilter('width_value', $width);
            $collection->addFieldToFilter('height_value', $height);
            $collection->addFieldToFilter('rim_value', $rim);
            $collection->setPageSize(10);
        }
        /* if($partsCategory == 'Battery'){
            $batteryDimension = $product->getAttributeText('battery_dimension');
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('entity_id', ['nin' => $productId]);
            $collection->addFieldToFilter('battery_dimension_value', $batteryDimension);
            $collection->setPageSize(10);
        }
        if($partsCategory == 'Brake Pad' || $partsCategory == 'Brake Shoe' || $partsCategory == 'Brake Fluid' || $partsCategory == 'Rim Protectors'){
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('entity_id', ['nin' => $productId]);
            $collection->addFieldToFilter('parts_category_value', $partsCategory);
            $collection->setPageSize(10);
        } */

        if($collection && $collection->getSize() > 0){
            
            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );

            $collection->each('setDoNotUseCategoryId', [true]);

            return $collection->getItems();
        }else{
            return [];
        }
    }

     public function getOnestepCheckoutVehcilelist()
    {
        // Old: External API call to Klever/wheel-size
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $vehicleModelUrl = "https://wheel-api.klever.ae/v1/makes/?user_key=".$wheelApiKey."&region=medm";
        // $vehicleMakes = file_get_contents($vehicleModelUrl);
        // if(empty($vehicleMakes)){
        //     $vehicleMakes_url = "https://api.wheel-size.com/v2/makes/?user_key=".$wheelApiKey."&region=medm";
        //     $vehicleMakes = file_get_contents($vehicleMakes_url);
        // }
        // $vehicleMakes =json_decode($vehicleMakes);

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getMakes(['ordering' => 'name']);
		$vehicle_list[] = array('value' => '', 'label' => __('Select Make'));
		foreach ($result['rows'] as $vehicle) {
			$vehicle_list[] = array('value' => $vehicle['slug'], 'label' => $vehicle['name']);
		}
        return $vehicle_list;
    }
}

