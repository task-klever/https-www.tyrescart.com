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
namespace Ecomteck\StoreLocator\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Api\Data;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;
use Ecomteck\StoreLocator\Api\Data\StoresSearchResultsInterfaceFactory;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores as ResourceStores;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory as StoresCollectionFactory;

/**
 * Class StoresRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoresRepository implements StoresRepositoryInterface
{
    /**
     * @var array
     */
    public $instances = [];
    /**
     * @var ResourceStores
     */
    public $resource;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var StoresCollectionFactory
     */
    public $storesCollectionFactory;
    /**
     * @var StoresSearchResultsInterfaceFactory
     */
    public $searchResultsFactory;
    /**
     * @var StoresInterfaceFactory
     */
    public $storesInterfaceFactory;
    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;

    public function __construct(
        ResourceStores $resource,
        StoreManagerInterface $storeManager,
        StoresCollectionFactory $storesCollectionFactory,
        StoresSearchResultsInterfaceFactory $storesSearchResultsInterfaceFactory,
        StoresInterfaceFactory $storesInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource                 = $resource;
        $this->storeManager             = $storeManager;
        $this->storesCollectionFactory  = $storesCollectionFactory;
        $this->searchResultsFactory     = $storesSearchResultsInterfaceFactory;
        $this->storesInterfaceFactory   = $storesInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
    }
    /**
     * Save page.
     *
     * @param \Ecomteck\StoreLocator\Api\Data\StoresInterface $stores
     * @return \Ecomteck\StoreLocator\Api\Data\StoresInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(StoresInterface $stores)
    {
        /** @var StoresInterface|\Magento\Framework\Model\AbstractModel $stores */
        if (empty($stores->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $stores->setStoreId($storeId);
        }
        try {
            $this->resource->save($stores);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the store: %1',
                $exception->getMessage()
            ));
        }
        return $stores;
    }

    /**
     * Retrieve Stores.
     *
     * @param int $storesId
     * @return \Ecomteck\StoreLocator\Api\Data\StoresInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($storesId)
    {
        if (!isset($this->instances[$storesId])) {

            /** @var \Ecomteck\StoreLocator\Api\Data\StoresInterface|\Magento\Framework\Model\AbstractModel $stores */
            $stores = $this->storesInterfaceFactory->create();
            $this->resource->load($stores, $storesId);
            
            if (!$stores->getId()) {
                throw new NoSuchEntityException(__('Requested stores doesn\'t exist'));

           }
            $this->instances[$storesId] = $stores;
        }

        return $this->instances[$storesId];;
    }

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Ecomteck\StoreLocator\Api\Data\StoresSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Ecomteck\StoreLocator\Api\Data\StoresSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Ecomteck\StoreLocator\Model\ResourceModel\Stores\Collection $collection */
        $collection = $this->storesCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'stores_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Ecomteck\StoreLocator\Api\Data\StoresInterface[] $storelocator */
        $storelocator = [];
        /** @var \Ecomteck\StoreLocator\Model\Stores $stores */
        foreach ($collection as $stores) {
            /** @var \Ecomteck\StoreLocator\Api\Data\StoresInterface $storesDataObject */
            $storesDataObject = $this->storesInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray($storesDataObject, $stores->getData(), StoresInterface::class);
            $storelocator[] = $storesDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($storelocator);
    }

    /**
     * Delete stores.
     *
     * @param \Ecomteck\StoreLocator\Api\Data\StoresInterface $stores
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(StoresInterface $stores)
    {
        /** @var \Ecomteck\StoreLocator\Api\Data\StoresInterface|\Magento\Framework\Model\AbstractModel $stores */
        $id = $stores->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($stores);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove stores %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete stores by ID.
     *
     * @param int $storesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($storesId)
    {
        $stores = $this->getById($storesId);
        return $this->delete($stores);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    public function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }

}
