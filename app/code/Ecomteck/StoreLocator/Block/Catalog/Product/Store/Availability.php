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
 
namespace Ecomteck\StoreLocator\Block\Catalog\Product\Store;
use Magento\Catalog\Block\Product\Context;

class Availability extends \Ecomteck\StoreLocator\Block\StoreLocator
{

    public function getConfig()
    {
        $config = parent::getConfig();
        if(isset($config['dataLocation'])){
            //$config['dataLocation'] = $this->getUrl('storelocator/ajax/stores',['product_id'=>$this->getProduct()->getId()]);
            unset($config['dataLocation']);
            $config['dataRaw'] = $this->getAvailableStores();
        }
        return $config;
    }

    public function getFilters()
    {
        return [];
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }
    
    public function getAvailableStores()
    {       
        if (!$this->hasData('available_stores')) {
            $collection = $this->collectionFactory->create();
            $productId = $this->getProduct()->getId();
            if($productId){
                $collection->addProductFilter($productId);
            }
            $availableStores = [];
            foreach ($collection as $stores) {
                $data = $stores->getData();
                $data['lat'] = $data['latitude'];
                $data['lng'] = $data['longitude'];
                $data['image'] = $stores->getImageUrl();
                $data['details_image'] = $stores->getDetailsImageUrl();
                $data['opening_hours_formated'] = $stores->getOpeningHoursFormated();
                $data['opening_hours'] = $stores->getOpeningHoursConfig();
                $data['special_opening_hours'] = $stores->getSpecialOpeningHoursConfig();
                $data['special_opening_hours_formated'] = $stores->getSpecialOpeningHoursFormated();
                $data['details_link'] = $stores->getStoresUrl();
                if(!$data['country']){
                    $data['country'] = '';
                }
                if(!$data['city']){
                    $data['city'] = '';
                }
                $availableStores[] = $data;
            }
            $this->setData('available_stores', $availableStores);
        }
        return $this->getData('available_stores');
    }
}