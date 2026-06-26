<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\Address;

/**
 * Customer Sales Address abstract resource
 */
abstract class AbstractAddress extends \Ecomteck\CustomerCustomAttributes\Model\ResourceModel\Sales\AbstractSales
{
    /**
     * Used us prefix to name of column table
     *
     * @var null | string
     */
    protected $_columnPrefix = null;

    /**
     * Attach data to models
     *
     * @param \Magento\Framework\DataObject[] $entities
     * @return $this
     */
    public function attachDataToEntities(array $entities)
    {
        $items = [];
        $itemIds = [];
        foreach ($entities as $item) {
            /** @var $item \Magento\Framework\DataObject */
            $itemIds[] = $item->getId();
            $items[$item->getId()] = $item;
        }

        if ($itemIds) {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable()
            )->where(
                "{$this->getIdFieldName()} IN (?)",
                $itemIds
            );
            $rowSet = $this->getConnection()->fetchAll($select);
            foreach ($rowSet as $row) {
                $items[$row[$this->getIdFieldName()]]->addData($row);
            }
        }

        return $this;
    }
}
