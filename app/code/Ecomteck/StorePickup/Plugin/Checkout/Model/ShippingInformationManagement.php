<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        $pickupDate = $extAttributes->getPickupDate();
        $pickupTime = $extAttributes->getPickupTime();
        $pickupStore = $extAttributes->getPickupStore();

        /* $vin = '';
        if ($extAttributes->getVinNumber() !== null) {
            $vin = ucwords($extAttributes->getVinNumber());
        } */
        $plate = '';
        if ($extAttributes->getPlate() !== null) {
            $plate = ucwords($extAttributes->getPlate());
        }
        $make = '';
        if ($extAttributes->getMake() !== null) {
            $make = ucwords($extAttributes->getMake());
        }
        $model = '';
        if ($extAttributes->getModel() !== null) {
            $model = ucwords($extAttributes->getModel());
        }
        $year = '';
        if ($extAttributes->getYear() !== null) {
            $year = ucwords($extAttributes->getYear());
        }

        $quote = $this->quoteRepository->getActive($cartId);        

       // $quote->setVinNumber($vin);
        $quote->setPlate($plate);
        $quote->setMake($make);
        $quote->setModel($model);
        $quote->setYear($year);
    }
}