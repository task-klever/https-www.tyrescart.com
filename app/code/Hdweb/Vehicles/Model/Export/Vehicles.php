<?php

namespace Hdweb\Vehicles\Model\Export;


use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Hdweb\Vehicles\Model\VehiclesFactory;
use Magento\Framework\Data\Collection;

class Vehicles extends AbstractEntity
{
    const ENTITY_CODE = 'vehicles';
    const ENTITY_ID_COLUMN = 'vehicles_id';
    const COL_STORE_ID = 'store_id';
    const COL_MAKE = 'make';
    const COL_MODEL = 'model';
    const COL_MAKE_PARAGRAPH1 = 'make_paragraph1';
    const COL_MAKE_PARAGRAPH2 = 'make_paragraph2';
    const COL_MODEL_PARAGRAPH1 = 'model_paragraph1';
    const COL_MODEL_PARAGRAPH2 = 'model_paragraph2';
    const COL_MODEL_PARAGRAPH3 = 'model_paragraph3';
    const COL_META_TITLE = 'meta_title';
    const COL_META_KEYWORDS = 'meta_keywords';
    const COL_META_DESCRIPTION = 'meta_description';
    const COL_STATUS = 'status';
    const COL_CREATED_BY = 'created_by';
    const COL_UPDATED_BY = 'updated_by';
    const TABLE = 'hdweb_vehicles';

    protected $_permanentAttributes = [
        self::ENTITY_ID_COLUMN,
        self::COL_STORE_ID,
        self::COL_MAKE,
        self::COL_MODEL,
        self::COL_MAKE_PARAGRAPH1,
        self::COL_MAKE_PARAGRAPH2,
        self::COL_MODEL_PARAGRAPH1,
        self::COL_MODEL_PARAGRAPH2,
        self::COL_MODEL_PARAGRAPH3,
        self::COL_META_TITLE,
        self::COL_META_KEYWORDS,
        self::COL_META_DESCRIPTION,
        self::COL_STATUS,
        self::COL_CREATED_BY,
        self::COL_UPDATED_BY
    ];

    protected $vehicles_factory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        VehiclesFactory $vehicles_factory,
        array $data = []
    ) {
        $this->vehicles_factory = $vehicles_factory;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    protected function _getHeaderColumns()
    {
        return $this->_permanentAttributes;
    }

    public function exportItem($item)
    {
        // will not implement this method as it is legacy interface
    }

    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }

    /* public function filterAttributeCollection(Collection $collection)
    {
        
    } */

    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $vehiclesCollection =  $this->vehicles_factory->create()->getCollection();
        foreach ($vehiclesCollection->getData() as $key => $data) {
            if ($key != 'created_at' || $key != 'updated_at') {
                $writer->writeRow($data);
            }
        }
        return $writer->getContents();
    }
}
