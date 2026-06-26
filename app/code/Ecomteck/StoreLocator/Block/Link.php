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
namespace Ecomteck\StoreLocator\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Ecomteck\StoreLocator\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ecomteck\StoreLocator\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        $url = $this->config->getModuleUrlSettings();
        return $this->getUrl($url);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return  __('Store Locator');
    }

    /**
     * {@inheritdoc}
     * @since 100.2.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
