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
// @codingStandardsIgnoreFile
namespace Ecomteck\StoreLocator\Model\ResourceModel\Stores\Grid;

use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;

/**
 * Stores collection backed by services
 */
class ServiceCollection extends AbstractServiceCollection
{
    /**
     * @var StoresRepositoryInterface
     */
    public $storesRepository;

    /**
     * @var SimpleDataObjectConverter
     */
    public $simpleDataObjectConverter;

    /**
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param StoresRepositoryInterface $storesRepository
     * @param SimpleDataObjectConverter $simpleDataObjectConverter
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StoresRepositoryInterface $storesRepository,
        SimpleDataObjectConverter $simpleDataObjectConverter
    ) {
        $this->storesRepository          = $storesRepository;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
    }

    /**
     * Load customer group collection data from service
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->storesRepository->getList($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            /** @var StoresInterface[] $storelocator */
            $storelocator = $searchResults->getItems();
            foreach ($storelocator as $stores) {
                $storesItem = new DataObject();
                $storesItem->addData(
                    $this->simpleDataObjectConverter->toFlatArray($stores, StoresInterface::class)
                );
                $this->_addItem($storesItem);
            }
            $this->_setIsLoaded();
        }
        return $this;
    }
}
