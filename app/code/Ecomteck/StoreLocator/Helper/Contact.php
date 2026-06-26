<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2017 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Ecomteck\Retailer\Api\Data\StoresInterface;

/**
 * Contact information Helper
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Contact extends AbstractHelper
{
    /**
     * Check if a store has contact information.
     *
     * @param StoresInterface $store The store
     *
     * @return bool
     */
    public function hasContactInformation($store)
    {
        return $store->hasData('email');
    }

    /**
     * Check if a store can display contact form.
     *
     * @param StoresInterface $store The store
     *
     * @return bool
     */
    public function canDisplayContactForm($store)
    {
        return true === (bool) $this->hasContactInformation($store);
    }
}
