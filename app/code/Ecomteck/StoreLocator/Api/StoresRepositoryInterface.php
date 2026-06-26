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
namespace Ecomteck\StoreLocator\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;

/**
 * @api
 */
interface StoresRepositoryInterface
{
    /**
     * Save page.
     *
     * @param StoresInterface $stores
     * @return StoresInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(StoresInterface $stores);

    /**
     * Retrieve Stores.
     *
     * @param int $storesId
     * @return StoresInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($storesId);

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomteck\StoreLocator\Api\Data\StoresSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete stores.
     *
     * @param StoresInterface $stores
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(StoresInterface $stores);

    /**
     * Delete stores by ID.
     *
     * @param int $storesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($storesId);
}
