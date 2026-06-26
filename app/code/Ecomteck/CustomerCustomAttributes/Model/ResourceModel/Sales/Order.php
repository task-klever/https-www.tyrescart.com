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
namespace Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales;

/**
 * Customer Order resource
 */
class Order extends AbstractSales
{
    /**
     * Main entity resource model
     *
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $_parentResourceModel;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order $parentResourceModel
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Sales\Model\ResourceModel\Order $parentResourceModel,
        $connectionName = null
    ) {
        $this->_parentResourceModel = $parentResourceModel;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magento_customercustomattributes_sales_flat_order', 'entity_id');
    }

    /**
     * Return resource model of the main entity
     *
     * @return \Magento\Sales\Model\ResourceModel\Order
     */
    protected function _getParentResourceModel()
    {
        return $this->_parentResourceModel;
    }
}
