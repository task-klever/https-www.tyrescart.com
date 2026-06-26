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
namespace Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\Order;

/**
 * Customer Order Address resource model
 */
class Address extends \Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\Address\AbstractAddress
{
    /**
     * Main entity resource model
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Address
     */
    protected $_parentResourceModel;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Address $parentResourceModel
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Address $parentResourceModel,
        $connectionName = null
    ) {
        $this->_parentResourceModel = $parentResourceModel;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initializes resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magento_customercustomattributes_sales_flat_order_address', 'entity_id');
    }

    /**
     * Return resource model of the main entity
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Address
     */
    protected function _getParentResourceModel()
    {
        return $this->_parentResourceModel;
    }
}
