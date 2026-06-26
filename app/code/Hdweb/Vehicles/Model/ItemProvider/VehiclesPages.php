<?php

namespace Hdweb\Vehicles\Model\ItemProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemFactory;


/**
 * Class VehiclesPages
 * @package Hdweb\Vehicles\Model\ItemProvider
 */
class VehiclesPages implements ItemProviderInterface
{
	/**
	 * @var VehiclesPagesConfigReader
	 */
	private $configReader;

	/**
	 * @var SitemapItemFactory
	 */
	private $itemFactory;

	/**
	 * @var array
	 */
	protected $sitemapItems = [];

	protected $vehicles;

	protected $_storeManager;

	/**
	 * VehiclesPages constructor.
	 * @param VehiclesPagesConfigReader $configReader
	 * @param SitemapItemFactory $itemFactory
	 */
	public function __construct(
		VehiclesPagesConfigReader $configReader,
		SitemapItemFactory $itemFactory,
		\Hdweb\Vehicles\Model\Vehicles $vehicles,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->configReader = $configReader;
		$this->itemFactory = $itemFactory;
		$this->vehicles = $vehicles;
		$this->_storeManager = $storeManager;
	}

	/**
	 * @param int $storeId
	 * @return array
	 * @throws NoSuchEntityException
	 */
	public function getItems($storeId): array
	{
		$vehicles = $this->vehicles;
		$vehiclesMakeCollection = $vehicles->getCollection()
			//->addFieldToFilter('status', 1)
			->addFieldToFilter('make', ['neq' => 'NULL'])
			/*->addFieldToFilter(
				'model',
				array('null' => true)
			)*/
			->addFieldToFilter(
			    'model',
			    [
			        ['null' => true],
			        ['eq' => ''],
			    ]
			)
			->addFieldToSelect('make')
			->addFieldToSelect('updated_at')
			//->addStoreFilter($this->_storeManager->getStore()->getId())
			->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
			->setOrder('make', 'ASC');

		$route = 'tyres/cars';

		$this->sitemapItems[] = $this->itemFactory->create(
			[
				'url' => 'tyres/cars',
				'updatedAt' => date("Y-m-d H:i:s"),
				'priority' => $this->getPriority($storeId),
				'changeFrequency' => $this->getChangeFrequency($storeId)
			]
		);

		foreach ($vehiclesMakeCollection as $makeData) {
			$makeUrl = $route . '/' . $makeData->getMake();
			$this->sitemapItems[] = $this->itemFactory->create(
				[
					'url' => $makeUrl,
					'updatedAt' => $makeData->getUpdatedAt(),
					'priority' => $this->getPriority($storeId),
					'changeFrequency' => $this->getChangeFrequency($storeId)
				]
			);
			$make = $makeData->getMake();
			$modelCollection = $vehicles->getCollection()
				->addFieldToSelect('model')
				->addFieldToSelect('updated_at')
				// ->addFieldToFilter('model', ['neq' => 'NULL'])
				->addFieldToFilter(
			        'model',
			        [
			            ['notnull' => true],
			            ['neq' => '']
			        ]
			    )
				->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
				->addFieldToFilter('make', $make);

			if (count($modelCollection->getData()) > 0) {
				foreach ($modelCollection as $modelData) {
					if (!empty($modelData->getModel())) {
						$modelUrl = $makeUrl . '/' . $modelData->getModel();
						$this->sitemapItems[] = $this->itemFactory->create(
							[
								'url' => $modelUrl,
								'updatedAt' => $modelData->getUpdatedAt(),
								'priority' => $this->getPriority($storeId),
								'changeFrequency' => $this->getChangeFrequency($storeId)
							]
						);
					}
				}
			}
		}

		return $this->sitemapItems;
	}

	/**
	 * @param int $storeId
	 *
	 * @return string
	 *
	 */
	private function getChangeFrequency(int $storeId): string
	{
		return $this->configReader->getChangeFrequency($storeId);
	}

	/**
	 * @param int $storeId
	 *
	 * @return string
	 *
	 */
	private function getPriority(int $storeId): string
	{
		return $this->configReader->getPriority($storeId);
	}
}
