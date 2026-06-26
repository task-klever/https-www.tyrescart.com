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
namespace Ecomteck\CustomerCustomAttributes\Model\Sales;

/**
 * Customer Quote model
 *
 * @method \Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\Quote _getResource()
 * @method \Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\Quote getResource()
 * @method \Ecomteck\CustomerCustomAttributes\Model\Sales\Quote setEntityId(int $value)
 *
 * @author      Ecomteck <ecomteck@gmail.com>
 */
class Quote extends AbstractSales
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\Quote');
    }
}
