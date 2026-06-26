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
 * @package   mirasvit/module-dashboard
 * @version   1.3.17
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Model\Block;

use Magento\Framework\DataObject;
use Mirasvit\Dashboard\Api\Data\BlockInterface;

class Config extends DataObject
{
    public function getRenderer(): string
    {
        $value = $this->getData('renderer');

        return $value ? $value : BlockInterface::RENDERER_SINGLE;
    }

    public function getSingle(): Single
    {
        $value = $this->getData(BlockInterface::RENDERER_SINGLE);

        return new Single($value ? $value : []);
    }

    public function getTable(): Table
    {
        $value = $this->getData(BlockInterface::RENDERER_TABLE);

        return new Table($value);
    }

    public function getChart(): Chart
    {
        $value = $this->getData(BlockInterface::RENDERER_CHART);

        return new Chart($value ? $value : []);
    }

    public function getHtml(): array
    {
        $value = $this->getData(BlockInterface::RENDERER_HTML);

        return $value;
    }

    public function getFilters(): array
    {
        $value = $this->getData('filters');

        return is_array($value) ? $value : [];
    }

    public function getDateRange(): DateRange
    {
        $value = $this->getData('date_range');

        return new DateRange($value ? $value : []);
    }
}
