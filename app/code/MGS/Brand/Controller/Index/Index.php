<?php

namespace MGS\Brand\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use MGS\Brand\Model\BrandFactory;
use Magento\Framework\UrlInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_brandHelper;
    protected $resultForwardFactory;
    protected $_registry;
    protected $_brandFactory;
    protected $_urlBuilder;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \MGS\Brand\Helper\Data $brandHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Registry $registry,
        BrandFactory $brandFactory,
        UrlInterface $urlBuilder
    )
    {
        $this->_brandHelper = $brandHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_registry = $registry;
        $this->_brandFactory = $brandFactory;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_brandHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($this->_brandHelper->getConfig('list_page_settings/template')) {
            $resultPage->getConfig()->setPageLayout($this->_brandHelper->getConfig('list_page_settings/template'));
        }

        /*$breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link'  => $this->_url->getUrl('')
                ]
            );

            $breadcrumbs->addCrumb(
                'brand',
                [
                    'label' => __('Brand'),
                    'title' => __('Brand'),
                    'link'  => $this->_urlBuilder->getUrl('brand') // ✅ fixed
                ]
            );
            $brandKey = $this->getRequest()->getParam('brand'); // e.g., 'accelera'

            // Load brand by url_key
            $brand = $this->_brandFactory->create()->load($brandKey, 'url_key');

            if (!$brand->getId()) {
                return $this->_forward('noroute'); // 404
            }

            // Register brand for other blocks
            $this->_registry->register('current_brand', $brand);
            $breadcrumbs->addCrumb(
                'brand_name',
                [
                    'label' => $brand->getName(),
                    'title' => $brand->getName()
                ]
            );
        }*/

        return $resultPage;
    }
}