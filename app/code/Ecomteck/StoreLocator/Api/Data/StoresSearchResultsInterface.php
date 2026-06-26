<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface StoresSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get stores list.
     *
     * @return \Ecomteck\StoreLocator\Api\Data\StoresInterface[]
     */
    public function getItems();

    /**
     * Set storelocator list.
     *
     * @param \Ecomteck\StoreLocator\Api\Data\StoresInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
