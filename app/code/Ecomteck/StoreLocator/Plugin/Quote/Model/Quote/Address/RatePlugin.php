<?php
declare(strict_types=1);

namespace Ecomteck\StoreLocator\Plugin\Quote\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Ecomteck\StoreLocator\Model\StoresFactory;
use Magento\Store\Model\ScopeInterface;

class RatePlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $checkoutSession;

    protected $storesFactory;

    /**
     * RatePlugin constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        StoresFactory $storesFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->storesFactory = $storesFactory;
    }

    /**
     * Check if the given store is the "no fitment" installer
     * and the cart qty qualifies for free shipping.
     */
    private function isNoFitmentFreeShipping($quote, $storeId): bool
    {
        $noFitmentId = $this->scopeConfig->getValue(
            'ecomteck_storelocator/installer/no_fitment_installer',
            ScopeInterface::SCOPE_STORE
        );

        if ($storeId && $noFitmentId && (int)$storeId === (int)$noFitmentId) {
            $freeShippingQty = (int) $this->scopeConfig->getValue(
                'ecomteck_storelocator/installer/without_fitment_free_shipping_qty',
                ScopeInterface::SCOPE_STORE
            ) ?: 2;

            if ($quote->getItemsQty() >= $freeShippingQty) {
                return true;
            }
        }

        return false;
    }

    /**
     * Plugin method to customize shipping rate import
     *
     * @param Rate $subject
     * @param Rate $result
     * @param AbstractResult $rate
     * @return Rate
     */
    public function afterImportShippingRate(
        Rate $subject,
        Rate $result,
        AbstractResult $rate
    ): Rate {
        
        if ($rate instanceof Method) {
            $quote = $this->checkoutSession->getQuote();
            $storeId = $quote->getPickupStore();
            if($storeId){
                // If this is the no-fitment store and qty qualifies for free shipping, set price to 0
                if ($this->isNoFitmentFreeShipping($quote, $storeId)) {
                    $result->setPrice(0);
                } else {
                    $store = $this->storesFactory->create()->load($storeId);
                    if($store->getShippingAmount()){
                        $shippingAmount = $store->getShippingAmount();
                        $result->setPrice($shippingAmount);
                    }
                }
            }else{
                $result->setPrice(0);
            }
        }

        return $result;
    }
}
