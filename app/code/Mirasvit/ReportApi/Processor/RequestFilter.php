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
 * @package   mirasvit/module-report-api
 * @version   1.0.73
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\ReportApi\Api\Processor\RequestFilterInterface;

class RequestFilter extends AbstractSimpleObject implements RequestFilterInterface
{
    const COLUMN         = 'column';
    const VALUE          = 'value';
    const CONDITION_TYPE = 'condition_type';
    const GROUP          = 'group';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($data);
    }

    /**
     * @param string $column
     * @return RequestFilterInterface|RequestFilter
     */
    public function setColumn($column)
    {
        return $this->setData(self::COLUMN, $column);
    }

    /**
     * @return mixed|string|null
     */
    public function getColumn()
    {
        return $this->_get(self::COLUMN);
    }

    /**
     * @param number|string $value
     * @return RequestFilterInterface|RequestFilter
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @return mixed|number|string|null
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * @param string $type
     * @return RequestFilterInterface|RequestFilter
     */
    public function setConditionType($type)
    {
        return $this->setData(self::CONDITION_TYPE, $type);
    }

    /**
     * @return mixed|string|null
     */
    public function getConditionType()
    {
        return $this->_get(self::CONDITION_TYPE);
    }

    /**
     * @param mixed $group
     * @return RequestFilterInterface|RequestFilter
     */
    public function setGroup($group)
    {
        return $this->setData(self::GROUP, $group);
    }

    /**
     * @return mixed|string|null
     */
    public function getGroup()
    {
        return $this->_get(self::GROUP);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->serializer->serialize($this->__toArray());
    }
}
