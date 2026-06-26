<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Model;

/**
 * Class WhitelistEntry
 *
 * @package BitExpert\ForceCustomerLogin\Model
 * @codingStandardsIgnoreFile
 */
class WhitelistEntry extends \Magento\Framework\Model\AbstractModel implements
    \BitExpert\ForceCustomerLogin\Api\Data\WhitelistEntryInterface
{
    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return (int) $this->getData(static::KEY_STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(static::KEY_STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->getData(static::KEY_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label)
    {
        return $this->setData(static::KEY_LABEL, $label);
    }

    /**
     * @inheritDoc
     */
    public function getUrlRule()
    {
        return $this->getData(static::KEY_URL_RULE);
    }

    /**
     * @inheritDoc
     */
    public function setUrlRule($urlRule)
    {
        return $this->setData(static::KEY_URL_RULE, $urlRule);
    }

    /**
     * @inheritDoc
     */
    public function getEditable()
    {
        return !!$this->getData(static::KEY_EDITABLE);
    }

    /**
     * @inheritDoc
     */
    public function setEditable($editable)
    {
        return $this->setData(static::KEY_EDITABLE, $editable);
    }

    /**
     * @inheritDoc
     */
    public function getStrategy()
    {
        return $this->getData(static::KEY_STRATEGY);
    }

    /**
     * @inheritDoc
     */
    public function setStrategy($strategy)
    {
        return $this->setData(static::KEY_STRATEGY, $strategy);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BitExpert\ForceCustomerLogin\Model\ResourceModel\WhitelistEntry');
    }
}
