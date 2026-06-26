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



namespace Mirasvit\ReportBuilder\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\ReportBuilder\Api\Data\ConfigInterface;

class Config extends AbstractModel implements ConfigInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\ReportBuilder\Model\ResourceModel\Config::class);
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

    /**
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $value
     * @return ConfigInterface|Config
     */
    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * @return array|mixed

     */
    public function getConfig()
    {
        $config = $this->getData(self::CONFIG);

        return $config ? $this->serializer->unserialize($config) : [];
    }

    /**
     * @param array $value
     * @return ConfigInterface|Config
     */
    public function setConfig($value)
    {
        return $this->setData(self::CONFIG, $this->serializer->serialize($value));
    }

    /**
     * @return int|mixed
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * @param int $value
     * @return ConfigInterface|Config
     */
    public function setUserId($value)
    {
        return $this->setData(self::USER_ID, $value);
    }
}
