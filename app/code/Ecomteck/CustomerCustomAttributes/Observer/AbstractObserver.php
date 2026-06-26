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
namespace Ecomteck\CustomerCustomAttributes\Observer;

abstract class AbstractObserver
{
    const CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX = 1;

    const CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX = 2;

    const CONVERT_ALGORITM_TARGET_WITHOUT_PREFIX = 3;

    const CONVERT_TYPE_CUSTOMER = 'customer';

    const CONVERT_TYPE_CUSTOMER_ADDRESS = 'customer_address';

    /**
     * @var \Ecomteck\CustomerCustomAttributes\Helper\Data
     */
    protected $_customerData;

    /**
     * @param \Ecomteck\CustomerCustomAttributes\Helper\Data $customerData
     */
    public function __construct(
        \Ecomteck\CustomerCustomAttributes\Helper\Data $customerData
    ) {
        $this->_customerData = $customerData;
    }

    /**
     * CopyFieldset converts customer attributes from source object to target object
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @param int $algorithm
     * @param string $convertType
     * @return $this
     */
    protected function _copyFieldset(
        \Magento\Framework\Event\Observer $observer,
        $algorithm = self::CONVERT_ALGORITM_TARGET_WITHOUT_PREFIX,
        $convertType = self::CONVERT_TYPE_CUSTOMER
    ) {
        $source = $observer->getEvent()->getSource();
        $target = $observer->getEvent()->getTarget();

        if ($source instanceof \Magento\Framework\DataObject &&
            $target instanceof \Magento\Framework\DataObject
        ) {
            if ($convertType == self::CONVERT_TYPE_CUSTOMER_ADDRESS) {
                $attributes = $this->_customerData->getCustomerAddressUserDefinedAttributeCodes();
                $prefix = '';
            } else {
                $attributes = $this->_customerData->getCustomerUserDefinedAttributeCodes();
                $prefix = 'customer_';
            }

            foreach ($attributes as $attribute) {
                switch ($algorithm) {
                    case self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX:
                        $sourceAttribute = $prefix . $attribute;
                        $targetAttribute = $prefix . $attribute;
                        break;
                    case self::CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX:
                        $sourceAttribute = $attribute;
                        $targetAttribute = $prefix . $attribute;
                        break;
                    case self::CONVERT_ALGORITM_TARGET_WITHOUT_PREFIX:
                    default:
                        $sourceAttribute = $prefix . $attribute;
                        $targetAttribute = $attribute;
                        break;
                }
                $target->setData($targetAttribute, $source->getData($sourceAttribute));
            }
        }

        return $this;
    }
}
