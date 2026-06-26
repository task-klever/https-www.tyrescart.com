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
namespace Ecomteck\CustomerCustomAttributes\Model\Sales\Address;

/**
 * Customer Address abstract model
 *
 */
abstract class AbstractAddress extends \Ecomteck\CustomerCustomAttributes\Model\Sales\AbstractSales
{
    /**
     * Attach data to models
     *
     * @param \Magento\Framework\DataObject[] $entities
     * @return $this
     */
    public function attachDataToEntities(array $entities)
    {
        $this->_getResource()->attachDataToEntities($entities);
        return $this;
    }
}
