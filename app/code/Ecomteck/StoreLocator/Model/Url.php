<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 * @copyright 2016 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Model;

use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Store URL model.
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Url
{
    /**
     * @var string
     */
    const BASE_URL_XML_PATH = 'ecomteck_storelocator/seo/url';

    /**
     * @var \Ecomteck\StoreLocator\Model\ResourceModel\Url
     */
    private $resourceModel;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filter;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor.
     *
     * @param \Ecomteck\StoreLocator\Model\ResourceModel\Url        $resourceModel ResourceModel.
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager  Store manager.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig   Store config.
     * @param \Magento\Framework\UrlInterface                    $urlBuilder    URL builder.
     * @param \Magento\Framework\Filter\FilterManager            $filter        Filters.
     */
    public function __construct(
        \Ecomteck\StoreLocator\Model\ResourceModel\Url $resourceModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Filter\FilterManager $filter
    ) {
        $this->resourceModel = $resourceModel;
        $this->storeManager  = $storeManager;
        $this->scopeConfig   = $scopeConfig;
        $this->urlBuilder    = $urlBuilder;
        $this->filter        = $filter;
    }

    /**
     * Get store URL key.
     *
     * @param StoresInterface $store Store.
     *
     * @return string
     */
    public function getUrlKey(StoresInterface $store)
    {
        $urlKey = !empty($store->getUrlKey()) ? $store->getUrlKey() : $store->getName();
        $this->filter->translitUrl($urlKey);

        return $this->filter->translitUrl($urlKey);
    }

    /**
     * Get store URL.
     *
     * @param StoresInterface $store Store.
     *
     * @return string
     */
    public function getUrl(StoresInterface $store)
    {
        if(!$store->getUrlKey()){
            return $this->urlBuilder->getUrl('storelocator/view/index', ['id'=>$store->getId()]);
        }
        $url = sprintf("%s/%s", $this->getRequestPathPrefix($store->getStoreId()), $this->getUrlKey($store));
        return $this->urlBuilder->getUrl(null, ['_direct' => $url]);
    }

    /**
     * Get store locator home URL.
     *
     * @param int|NULL $storeId Store Id
     *
     * @return string
     */
    public function getHomeUrl($storeId = null)
    {
        return $this->urlBuilder->getUrl(null, ['_direct' => $this->getRequestPathPrefix($storeId)]);
    }

    /**
     * Get URL prefix for the store locator.
     *
     * @param int|NULL $storeId Store Id
     *
     * @return string
     */
    public function getRequestPathPrefix($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $this->scopeConfig->getValue(self::BASE_URL_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check an URL key exists and returns the store id. False if no store found.
     *
     * @param urlKey $urlKey  URL key.
     * @param int    $storeId Store Id.
     *
     * @return int|false
     */
    public function checkIdentifier($urlKey, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $this->resourceModel->checkIdentifier($urlKey, $storeId);
    }
}
