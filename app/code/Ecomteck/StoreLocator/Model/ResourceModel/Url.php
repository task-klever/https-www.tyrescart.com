<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2016 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Model\ResourceModel;

use Magento\Framework\DB\Select;

/**
 * Retailer URL resource model.
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Url extends \Ecomteck\StoreLocator\Model\ResourceModel\Stores
{
    /**
     * Check an URL key exists and returns the retailer id. False if no retailer found.
     *
     * @param urlKey $urlKey  URL key.
     * @param int    $storeId Store Id.
     *
     * @return int|false
     */
    public function checkIdentifier($urlKey, $storeId)
    {
        $urlKeyAttribute = '';
        $select = $this->getConnection()->select();

        $select->from($this->getTable('ecomteck_storelocator_stores'), ['stores_id'])
            ->where('url_key = ?', $urlKey)
            //->where('store_id IN(?, 0)', $storeId)
            ->order('store_id ' . Select::SQL_DESC)
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}
