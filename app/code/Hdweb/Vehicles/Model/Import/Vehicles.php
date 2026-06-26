<?php

namespace Hdweb\Vehicles\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Hdweb\Vehicles\Model\Vehicles as VehiclesModel;

/**
 * Class Courses
 */
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

    /**
     * If we should check column names
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     */
    protected $_permanentAttributes = [
        self::ENTITY_ID_COLUMN
    ];

    /**
     * Valid column names
     */
    protected $validColumnNames = [
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

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    protected $vehiclesModel;

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        VehiclesModel $vehiclesModel
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->vehiclesModel = $vehiclesModel;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        $this->countItemsCreated = 0; // Initialize the counter for created items
        $this->countItemsUpdated = 0; // Initialize the counter for updated items

        $bunch = $this->_dataSourceModel->getNextBunch();

        foreach ($bunch as $rowNum => $row) {
            if (!$this->validateRow($row, $rowNum)) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID, $rowNum);
                continue;
            }

            if ($this->getErrorAggregator()->hasToBeTerminated()) {
                $this->getErrorAggregator()->addRowToSkip($rowNum);
                continue;
            }

            // Prepare data for insertion or update
            $data = [];
            foreach ($row as $column => $value) {
                if (!empty($value)) {
                    $data[$column] = $value;
                }
            }

            // Check if vehicles_id exists and update or insert accordingly
            if (!empty($data[self::ENTITY_ID_COLUMN])) {
                // Check if record exists
                $existingRecord = $this->vehiclesModel->load($data[self::ENTITY_ID_COLUMN]);
                if ($existingRecord->getId()) {
                    // Update existing record
                    try {
                        $existingRecord->setData($data)->save();
                        $this->countItemsUpdated++;
                    } catch (\Exception $e) {
                        // Handle update errors
                        $this->addRowError("Error updating record: " . $e->getMessage(), $rowNum);
                        continue;
                    }
                } else {
                    // Insert new record
                    try {
                        $this->connection->insert(
                            $this->resource->getTableName(self::TABLE),
                            $data
                        );
                        $this->countItemsCreated++;
                    } catch (\Exception $e) {
                        // Handle insertion errors
                        $this->addRowError("Error inserting record: " . $e->getMessage(), $rowNum);
                        continue;
                    }
                }
            } else {
                // Insert new record
                try {
                    $this->connection->insert(
                        $this->resource->getTableName(self::TABLE),
                        $data
                    );
                    $this->countItemsCreated++;
                } catch (\Exception $e) {
                    // Handle insertion errors
                    $this->addRowError("Error inserting record: " . $e->getMessage(), $rowNum);
                    continue;
                }
            }
        }

        return true;
    }



    /**
     * Construct the update values string for ON DUPLICATE KEY UPDATE clause
     *
     * @param array $row
     * @return string
     */
    private function constructUpdateValues(array $row): string
    {
        $updates = [];
        foreach ($row as $key => $value) {
            if ($key !== self::ENTITY_ID_COLUMN) {
                $updates[] = "`$key` = VALUES(`$key`)";
            }
        }
        return implode(", ", $updates);
    }




    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }
}
