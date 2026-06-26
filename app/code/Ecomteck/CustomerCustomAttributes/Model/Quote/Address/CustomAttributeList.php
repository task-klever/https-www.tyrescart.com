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
namespace Ecomteck\CustomerCustomAttributes\Model\Quote\Address;

class CustomAttributeList implements \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface
{
    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var \Magento\Framework\Api\MetadataObjectInterface[]
     */
    protected $attributes = null;

    /**
     * @param \Magento\Customer\Api\AddressMetadataInterface $addressMetadata
     */
    public function __construct(\Magento\Customer\Api\AddressMetadataInterface $addressMetadata)
    {
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * Retrieve list of quote address custom attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            $this->attributes = [];
            $customAttributesMetadata = $this->addressMetadata->getCustomAttributesMetadata(
                '\Magento\Customer\Api\Data\AddressInterface'
            );
            if (is_array($customAttributesMetadata)) {
                /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
                foreach ($customAttributesMetadata as $attribute) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
            $customAttributesMetadata = $this->addressMetadata->getCustomAttributesMetadata(
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
            if (is_array($customAttributesMetadata)) {
                /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
                foreach ($customAttributesMetadata as $attribute) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }
        return $this->attributes;
    }
}
