<?php
/**
 * Ecomteck_StorePickup Magento Extension
 *
 * @category    Ecomteck
 * @package     Ecomteck_StorePickup
 * @author      Ecomteck <ecomteck@gmail.com>
 * @website    http://www.ecomteck.com
 */

namespace Ecomteck\StorePickup\Helper;

use \Magento\Framework\App\Helper\Context;
use \Ecomteck\StoreLocator\Api\StoresRepositoryInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    
    /**
     * @var StoresRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param StoresRepositoryInterface $storeRepository
     */
    public function __construct(
    	Context $context,
        StoresRepositoryInterface $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
        parent::__construct($context);
    }

    /**
     * Return store name by id
     *
     * @return string|null
     */
	public function getStoreNameById($storeId)
	{
        $store = $this->storeRepository->getById($storeId);
		if($store->getId()) {
			return $store->getName();
		}
	}
}