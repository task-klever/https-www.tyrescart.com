<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory;
use Psr\Log\LoggerInterface;

class DataAssignObserver implements ObserverInterface
{
    protected $quoteRepository;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $regionFactory;
    private $logger;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        CollectionFactory $collectionFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->collectionFactory = $collectionFactory;
        $this->regionFactory = $regionFactory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();

        // copy simple fields
        $order->setPickupDate($quote->getPickupDate());
        $order->setPickupTime($quote->getPickupTime());
        $order->setPickupLocation($quote->getPickupLocation());
        $order->setPlate($quote->getPlate());
        $order->setMake($quote->getMake());
        $order->setModel($quote->getModel());
        $order->setYear($quote->getYear());

        if ($quote->getCustomerComment()) {
            $order->setCustomerComment($quote->getCustomerComment());
        }

        // Default description if no store
        $shippingDescription = 'Delivery - Without Fitment';

        // If pickup store present on quote, try to load store info and determine description
        $pickupStoreId = (int)$quote->getPickupStore();
        if ($pickupStoreId > 0) {
            $order->setPickupStore($pickupStoreId);

            try {
                $collection = $this->collectionFactory->create();
                $collection->addActiveFilter();
                $collection->addFieldToFilter('stores_id', $pickupStoreId);
                $collection->setPageSize(1);

                $storeInfo = $collection->getFirstItem();
                $storeData = $storeInfo->getData();

                if ($storeData && isset($storeData['stores_id'])) {
                    // determine description
                    $isMobileVan = isset($storeData['ismobilevan']) ? (int)$storeData['ismobilevan'] : 0;
                    if ($isMobileVan === 1) {
                        $shippingDescription = 'Mobile Van Service';
                    } else {
                        $shippingDescription = 'Install at Outlet';
                    }

                    // set shipping address fields on order shipping address if exists
                    $countryId = isset($storeData['country']) ? $storeData['country'] : 'AE';
                    $city = isset($storeData['city']) ? $storeData['city'] : '';
                    $postcode = isset($storeData['postcode']) ? $storeData['postcode'] : '';
                    $region = isset($storeData['region']) ? $storeData['region'] : '';
                    $regionId = $this->getRegionByName($region, $countryId);
                    $street = isset($storeData['address']) ? $storeData['address'] : '';

                    $shippingAddress = $order->getShippingAddress();
                    if ($shippingAddress) {
                        $shippingAddress->setCountryId((string)$countryId)
                                        ->setCity((string)$city)
                                        ->setPostcode((string)$postcode)
                                        ->setRegionId($regionId !== "" ? (string)$regionId : null)
                                        ->setRegion((string)$region)
                                        ->setStreet($street)
                                        ->setCollectShippingRates(true);
                        // do not call ->save() here; let Magento persist with the order save
                    }
                } else {
                    // store not found: fallback description is Delivery - Without Fitment
                    $shippingDescription = 'Delivery - Without Fitment';
                }
            } catch (\Exception $e) {
                // log but don't break order flow
                $this->logger->error('StorePickup observer error: ' . $e->getMessage());
                $shippingDescription = 'Delivery - Without Fitment';
            }
        } else {
            // No pickup store - keep default 'Delivery - Without Fitment'
            $shippingDescription = 'Delivery - Without Fitment';
        }

        // set shipping description on order and on shipping address (if present)
        $order->setShippingDescription($shippingDescription);
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shippingAddress->setShippingDescription($shippingDescription);
        }

        return $this;
    }

    public function getRegionByName($region, $countryId)
    {
        try {
            $regionObject = $this->regionFactory->create()->loadByName($region, $countryId);
            if ($regionObject && $regionObject->getRegionId()) {
                return $regionObject->getRegionId();
            }
        } catch (\Exception $e) {
            // ignore
        }
        return "";
    }
}