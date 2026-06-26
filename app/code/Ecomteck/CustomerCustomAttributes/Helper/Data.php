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
namespace Ecomteck\CustomerCustomAttributes\Helper;

/**
 *  Customer Data Helper
 *
 */
class Data extends \Ecomteck\CustomAttributeManagement\Helper\Data
{
    /**
     * Customer customer
     *
     * @var Customer
     */
    protected $_customerCustomer = null;

    /**
     * Customer address
     *
     * @var Address
     */
    protected $_customerAddress = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param Address $customerAddress
     * @param Customer $customerCustomer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filter\FilterManager $filterManager,
        Address $customerAddress,
        Customer $customerCustomer
    ) {
        $this->_customerAddress = $customerAddress;
        $this->_customerCustomer = $customerCustomer;
        parent::__construct($context, $eavConfig, $localeDate, $filterManager);
    }

    /**
     * Return available customer attribute form as select options
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeFormOptions()
    {
        throw new \Magento\Framework\Exception\LocalizedException(__('Use helper with defined EAV entity.'));
    }

    /**
     * Default attribute entity type code
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEntityTypeCode()
    {
        throw new \Magento\Framework\Exception\LocalizedException(__('Use helper with defined EAV entity.'));
    }

    /**
     * Return available customer attribute form as select options
     *
     * @return array
     */
    public function getCustomerAttributeFormOptions()
    {
        return $this->_customerCustomer->getAttributeFormOptions();
    }

    /**
     * Return available customer address attribute form as select options
     *
     * @return array
     */
    public function getCustomerAddressAttributeFormOptions()
    {
        return $this->_customerAddress->getAttributeFormOptions();
    }

    /**
     * Returns array of user defined attribute codes for customer entity type
     *
     * @return array
     */
    public function getCustomerUserDefinedAttributeCodes()
    {
        return $this->_customerCustomer->getUserDefinedAttributeCodes();
    }

    /**
     * Returns array of user defined attribute codes for customer address entity type
     *
     * @return array
     */
    public function getCustomerAddressUserDefinedAttributeCodes()
    {
        return $this->_customerAddress->getUserDefinedAttributeCodes();
    }
}
