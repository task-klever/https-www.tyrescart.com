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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class GetStores extends \Magento\Framework\App\Action\Action
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
    protected $scopeConfig;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $collectionFactory,
        CheckoutSession $checkoutSession,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession   = $checkoutSession;
        $this->regionFactory     = $regionFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/storelocator_index_index.xml
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $mobileVanfittingInstallerId = $this->scopeConfig->getValue('ecomteck_storelocator/installer/mobile_van_installer', ScopeInterface::SCOPE_STORE);
        $nofitmentInstallerId = $this->scopeConfig->getValue('ecomteck_storelocator/installer/no_fitment_installer', ScopeInterface::SCOPE_STORE);
        $postData = $this->getRequest()->getPostValue();
        $installerType = $postData['installer_type'];

        if ($installerType == 'without_fitting') {
            $pickup_date = date('m/d/y', strtotime(' + 3 day'));
            $pickup_time  = '11:00am';
            $quote = $this->checkoutSession->getQuote();
            $quote->setPickupDate($pickup_date);
            $quote->setPickupTime($pickup_time);
            $quote->setPickupStore($nofitmentInstallerId);

            // --- Add shipping description ---
            $shippingAddress = $quote->getShippingAddress();
            if (!$shippingAddress || !$shippingAddress->getId()) {
                // If the quote has no shipping address yet, create one
                $shippingAddress = $quote->getShippingAddress() ?: $quote->addAddress();
                $shippingAddress->setAddressType('shipping');
            }
            
            $shippingAddress->setShippingMethod('storepickup_storepickup'); // optional code

            $description = 'Delivery - Without Fitment';
            $shippingAddress->setShippingDescription($description);
            $shippingAddress->setCollectShippingRates(false);

            // Reset shipping first, then apply only if qty is below threshold
            $shippingAddress->setShippingAmount(0);
            $shippingAddress->setBaseShippingAmount(0);

            $freeShippingQty = (int) $this->scopeConfig->getValue(
                'ecomteck_storelocator/installer/without_fitment_free_shipping_qty',
                ScopeInterface::SCOPE_STORE
            ) ?: 2;
            if ($quote->getItemsQty() < $freeShippingQty) {
                $noFitmentStore = $this->collectionFactory->create()
                    ->addFieldToFilter('stores_id', $nofitmentInstallerId)
                    ->getFirstItem();
                $shippingAmount = $noFitmentStore->getShippingAmount();
                if ($shippingAmount > 0) {
                    $shippingAddress->setShippingAmount($shippingAmount);
                    $shippingAddress->setBaseShippingAmount($shippingAmount);
                }
            }

            // optional: if you want to ensure it's also saved on quote
            $shippingAddress->save();

            $quote->save();

            $response = [
                'status' => 'success',
                'message' => 'No Fitment Installer Selected'
            ];
            return $this->resultJsonFactory->create()->setData($response);
        } else {
            $pickup_date = NULL;
            $pickup_time  = NULL;
            $pickup_store = NULL;
            $quote = $this->checkoutSession->getQuote();
            $quote->setPickupDate($pickup_date);
            $quote->setPickupTime($pickup_time);
            $quote->setPickupStore($pickup_store);

            // --- Add shipping description ---
            $shippingAddress = $quote->getShippingAddress();
            if (!$shippingAddress || !$shippingAddress->getId()) {
                // If the quote has no shipping address yet, create one
                $shippingAddress = $quote->getShippingAddress() ?: $quote->addAddress();
                $shippingAddress->setAddressType('shipping');
            }
            
            $shippingAddress->setShippingMethod('storepickup_storepickup'); // optional code

            if ($installerType == 'mobile_van') {
                $description = 'Mobile Van Service';
            }else {
                $description = 'Install at Outlet';
            }

            $shippingAddress->setShippingDescription($description);
            $shippingAddress->setCollectShippingRates(false);

            // Reset shipping amount when switching away from "without fitment"
            $shippingAddress->setShippingAmount(0);
            $shippingAddress->setBaseShippingAmount(0);

            // optional: if you want to ensure it's also saved on quote
            $shippingAddress->save();

            $quote->save();

        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('stores_id', ['neq' => $nofitmentInstallerId])
                   ->addFieldToFilter('status', 1);
        //  if ($installerType == 'mobile_van') {
        //     $collection->addFieldToFilter('stores_id', ['in' => $mobileVanfittingInstallerId]);
        // } else {
        //     $collection->addFieldToFilter('stores_id', ['nin' => $mobileVanfittingInstallerId]);
        // }

         if ($installerType == 'mobile_van') {
            $collection->addFieldToFilter('ismobilevan', 1);
        } else {
            $collection->addFieldToFilter('ismobilevan', 0);
        }

        $html = '<option value="">'.__('Select Location').'</option>';
        $storeSkipDays = [];
        $storeCutoffTime = [];
        $storeSkipHours = [];
        $storeGoogleMaps = [];
        $storeCities = [];

        foreach ($collection as $store) {

            $installerId = $store->getStoresId();
            $installerCity = "TBD if no city is set";

            $isComingSoon = $store->getCommingSoon(); // 0 or 1

            if ($store->getCity()) {
                $installerCity = $store->getCity();
            }

            // If Coming Soon → modify label + disable option
            if ($isComingSoon) {

                $installerCity .= ' - Opening Soon';

                $html .= '<option value="' . $installerId . '" 
                                data-store-id="' . $installerId . '" 
                                disabled="disabled">' 
                                . $installerCity . 
                        '</option>';

                continue;
            }

            // Normal case (not coming soon)
            $shipping_amount = $store->getShippingAmount();
            if ($shipping_amount > 0) {
                $installerCity .= ' - <span class="currency-dirham">AED </span>' . number_format($shipping_amount, 2);
            }

            $html .= '<option value="' . $installerId . '" data-store-id="' . $installerId . '">' . $installerCity . '</option>';

            // Collect scheduling data
            $storeSkipDays[$installerId] = $store->getSkipDays() ? (int)$store->getSkipDays() : 0;
            $storeCutoffTime[$installerId] = $store->getCutoffTime() ?: '';
            $storeSkipHours[$installerId] = $store->getSkipHours() ? (int)$store->getSkipHours() : 0;
            $storeGoogleMaps[$installerId] = $store->getGoogleMap() ?: '';
            $storeCities[$installerId] = $store->getCity() ?: '';
        }
        $response['html'] = $html;
        $response['store_skip_days'] = $storeSkipDays;
        $response['store_cutoff_time'] = $storeCutoffTime;
        $response['store_skip_hours'] = $storeSkipHours;
        $response['store_google_maps'] = $storeGoogleMaps;
        $response['store_cities'] = $storeCities;

        return $this->resultJsonFactory->create()->setData($response);
    }
}
