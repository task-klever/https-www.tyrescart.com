<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Model\Plugin;

class ConvertQuoteAddressToCustomerAddress
{
    /**
     * @var \Ecomteck\CustomerCustomAttributes\Helper\Data
     */
    private $customerData;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Helper\Data $customerData
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Helper\Data $customerData
    ) {
        $this->customerData = $customerData;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $quoteAddress
     * @param \Magento\Customer\Api\Data\AddressInterface $customerAddress
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function afterExportCustomerAddress(
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress,
        \Magento\Customer\Api\Data\AddressInterface $customerAddress
    ) {
        $attributes = $this->customerData->getCustomerAddressUserDefinedAttributeCodes();
        foreach ($attributes as $attribute) {
            $customerAddress->setCustomAttribute($attribute, $quoteAddress->getData($attribute));
        }
        return $customerAddress;
    }
}
