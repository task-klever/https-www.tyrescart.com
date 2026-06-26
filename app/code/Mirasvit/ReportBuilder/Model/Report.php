<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-builder
 * @version   1.1.8
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\ReportBuilder\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Report\Api\Data\ReportInterface as CoreReportInterface;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;

class Report extends AbstractModel implements ReportInterface
{
    public function getIdentifier(): string
    {
        return (string)$this->getId();
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $value): CoreReportInterface
    {
        return $this->setData(self::NAME, $value);
    }

    public function getUserId(): int
    {
        return (int)$this->getData(self::USER_ID);
    }

    public function setUserId(int $value): CoreReportInterface
    {
        return $this->setData(self::USER_ID, $value);
    }

    public function getTable(): string
    {
        if ($this->getDimensions()) {
            list($table,) = explode('|', $this->getDimensions()[0]);
        } else {
            return 'sales_order';
        }

        return $table;
    }

    /** STATE */
    public function getColumns(): ?array
    {
        return $this->getConfigValue(self::COLUMNS, []);
    }

    public function setColumns(array $value): CoreReportInterface
    {
        return $this->setConfigValue(self::COLUMNS, $value);
    }

    public function getDimensions(): array
    {
        $value = $this->getConfigValue(self::DIMENSIONS, []);

        return is_array($value) ? $value : [$value];
    }

    public function setDimensions(array $value): CoreReportInterface
    {
        return $this->setConfigValue(self::DIMENSIONS, $value);
    }

    public function getInternalColumns(): array
    {
        return $this->getConfigValue(self::INTERNAL_COLUMNS, []);
    }

    public function setInternalColumns(array $value): CoreReportInterface
    {
        return $this->setConfigValue(self::INTERNAL_COLUMNS, $value);
    }

    public function getInternalFilters(): array
    {
        return $this->getConfigValue(self::INTERNAL_FILTERS, []);
    }

    public function setInternalFilters(array $value): CoreReportInterface
    {
        return $this->setConfigValue(self::INTERNAL_FILTERS, $value);
    }

    public function getFilters(): ?array
    {
        return $this->getConfigValue(self::FILTERS);
    }

    public function setFilters(array $filters): CoreReportInterface
    {
        return $this->setConfigValue(self::FILTERS, array_values($filters));
    }

    /** SCHEMA */
    public function getPrimaryDimensions(): array
    {
        return $this->getConfigValue(self::PRIMARY_DIMENSIONS, []);
    }

    public function setPrimaryDimensions(array $value): CoreReportInterface
    {
        return $this->setConfigValue(self::PRIMARY_DIMENSIONS, $value);
    }

    public function getPrimaryFilters(): array
    {
        return $this->getConfigValue(self::PRIMARY_FILTERS, []);
    }

    public function setPrimaryFilters(array $value): CoreReportInterface
    {
        return $this->setConfigValue(self::PRIMARY_FILTERS, $value);
    }

    /**
     * @return \Mirasvit\Report\Model\GridConfig|void
     */
    public function getGridConfig()
    {
        // TODO: Implement getGridConfig() method.
    }

    ////

    /**
     * @return \Mirasvit\Report\Model\ChartConfig|void
     */
    public function getChartConfig()
    {
        // TODO: Implement getChartConfig() method.
    }

    /**
     * @param string $tableName
     *
     * @return ReportInterface|void
     */
    public function setTable($tableName)
    {
        // TODO: Implement setTable() method.
    }

    /**
     * @return ReportInterface|void
     */
    public function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    protected function _construct() {
        $this->_init(ResourceModel\Report::class);
    }
    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }

    private function getConfig(): array
    {
        $config = $this->getData(self::CONFIG);

        $config = $config ? $this->serializer->unserialize($config) : [];

        return $config;
    }

    private function setConfig(array $value): CoreReportInterface
    {
        return $this->setData(self::CONFIG, $this->serializer->serialize($value));
    }

    /**
     * @param string $key
     * @param mixed|null   $default
     *
     * @return mixed|null
     */
    private function getConfigValue(string $key, $default = null)
    {
        $config = $this->getConfig();

        return isset($config[$key]) ? $config[$key] : $default;
    }

    /**
     * @param string       $key
     * @param string|array $value
     *
     * @return Report
     */
    private function setConfigValue(string $key, $value): CoreReportInterface
    {
        $config       = $this->getConfig();
        $config[$key] = $value;

        return $this->setConfig($config);
    }

    public function getReportIdentifier(): ?string
    {
        return $this->getData(self::REPORT_IDENTIFIER) ?: null;
    }

    public function setReportIdentifier(string $identifier): CoreReportInterface
    {
        return $this->setData(self::REPORT_IDENTIFIER, $identifier);
    }

    public function getIsSharingEnabled(): bool
    {
        return (bool)$this->getData(self::SHARE_ENABLED);
    }

    public function setIsSharingEnabled(bool $value): CoreReportInterface
    {
        return $this->setData(self::SHARE_ENABLED, $value);
    }

    public function getShareIdentifier(): ?string
    {
        return $this->getData(self::SHARE_IDENTIFIER) ?: null;
    }

    public function setShareIdentifier(string $value): CoreReportInterface
    {
        return $this->setData(self::SHARE_IDENTIFIER, $value);
    }

    public function getSortOrders(): array
    {
        return $this->getConfigValue(self::SORT_ORDERS, []);
    }

    public function setSortOrders(array $orders): CoreReportInterface
    {
        return $this->setConfigValue(self::SORT_ORDERS, $orders);
    }

    public function getIsCustomized(): bool
    {
        return (bool)$this->getData(self::IS_CUSTOMIZED);
    }

    public function setIsCustomized(bool $value): CoreReportInterface
    {
        return $this->setData(self::IS_CUSTOMIZED, $value);
    }

    public function getPageSize(): int
    {
        return (int)$this->getConfigValue(self::PAGE_SIZE, 20);
    }

    public function setPageSize(int $value): CoreReportInterface
    {
        return $this->setConfigValue(self::PAGE_SIZE, $value);
    }

    public function getTimeRange(): ?string
    {
        return $this->getConfigValue(self::TIME_RANGE, null);
    }

    public function setTimeRange(string $value): CoreReportInterface
    {
        return $this->setConfigValue(self::TIME_RANGE, $value);
    }
}
