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

namespace Ecomteck\StoreLocator\Controller\Ajax;

use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Checkout\Model\Session as CheckoutSession;
/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Stores extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;    
    
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /** @var CheckoutSession */
    protected $checkoutSession;
    protected $regionFactory;
    
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $collectionFactory,
        CheckoutSession $checkoutSession,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->regionFactory = $regionFactory;
        parent::__construct($context);
    }
    
    /**
     * Load the page defined in view/frontend/layout/storelocator_index_index.xml
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {        
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter();
        $productId = $this->getRequest()->getParam('product_id');
        $checkQuote = $this->getRequest()->getParam('quote');
        if($productId){
            $collection->addProductFilter($productId);
        }
        if($checkQuote){
            $quote = $this->checkoutSession->getQuote(); 
            $productIds = [];
            foreach($quote->getAllVisibleItems() as $item){
                $productIds[] = $item->getProductId();
            }
            $collection->addProductsFilter($productIds);
        }
        $json = [];
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
            $data['country'] = $stores->getCountryName();
            $data['country_id'] = $stores->getCountry();
            $data['region_id'] = $this->getRegionByName($data['region'], $data['country_id']);
            unset($data['distance']);
            if(!$data['country']){
                $data['country'] = '';
            }
            if(!$data['city']){
                $data['city'] = '';
            }
            $json[] = $data;
        }
        return  $this->resultJsonFactory->create()->setData($json);
    }
    public function getRegionByName($region, $countryId) {
        $region_object = $this->regionFactory->create()->loadByName($region, $countryId);
        if($region_object){
            return $region_object->getRegionId();
        }
        return "";
    }
}
