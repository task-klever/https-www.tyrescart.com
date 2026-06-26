<?php

namespace MGS\Brand\Controller\Brand;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use MGS\Brand\Model\Layer\Resolver;

class View extends \Magento\Framework\App\Action\Action
{
    protected $_request;
    protected $_response;
    protected $resultRedirectFactory;
    protected $resultFactory;
    protected $_brandModel;
    protected $_coreRegistry = null;
    private $layerResolver;
    protected $resultForwardFactory;
    protected $_brandHelper;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MGS\Brand\Model\Brand $brandModel,
        \Magento\Framework\Registry $coreRegistry,
        Resolver $layerResolver,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \MGS\Brand\Helper\Data $brandHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandModel = $brandModel;
        $this->layerResolver = $layerResolver;
        $this->_coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_brandHelper = $brandHelper;
    }

    // public function _initBrand()
    // {
    //     $brandId = (int)$this->getRequest()->getParam('brand_id', false);
    //     if (!$brandId) {
    //         return false;
    //     }
    //     try {
    //         $brand = $this->_brandModel->load($brandId);
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    //     $this->_coreRegistry->register('current_brand', $brand);
    //     return $brand;
    // }

    public function _initBrand()
    {
        $urlKey = $this->getRequest()->getParam('brand'); // parameter passed in URL
        if (!$urlKey) {
            // fallback: maybe from URL path
            $pathInfo = trim($this->getRequest()->getPathInfo(), '/'); // e.g., shop-by-brand/accelera
            $parts = explode('/', $pathInfo);
            if (isset($parts[1])) {
                $urlKey = $parts[1]; // accelera
            } else {
                return false;
            }
        }

        try {
            $brand = $this->_brandModel->getCollection()
                ->addFieldToFilter('url_key', $urlKey)
                ->getFirstItem();
            if (!$brand || !$brand->getId()) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        $this->_coreRegistry->register('current_brand', $brand);
        
        return $brand;
    }

    public function execute()
    {
        if (!$this->_brandHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $brand = $this->_initBrand();
        if (!$brand) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $page = $this->resultPageFactory->create();
        $template = $this->_brandHelper->getConfig('view_page_settings/template');
        if ($template) {
            $page->getConfig()->setPageLayout($template);
        }

        $page->getConfig()->addBodyClass('brand-' . $brand->getUrlKey());

        return $page;
    }
}