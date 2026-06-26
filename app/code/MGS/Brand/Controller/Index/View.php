<?php
namespace MGS\Brand\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    protected $resultPageFactory;
    protected $brandFactory;
    protected $_registry;
    protected $_url;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \MGS\Brand\Model\BrandFactory $brandFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->brandFactory = $brandFactory;
        $this->_registry = $registry;
        $this->_url = $url;
        parent::__construct($context);
    }

    public function execute()
    {
        $brandKey = $this->getRequest()->getParam('brand'); // accelera
        $brand = $this->brandFactory->create()->load($brandKey, 'url_key');

        if (!$brand || !$brand->getId()) {
            $this->_forward('noroute');
            return;
        }

        $this->_registry->register('current_brand', $brand);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($brand->getName());

        // Breadcrumbs
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link'  => $this->_url->getUrl('')
            ]);
            $breadcrumbs->addCrumb('shop_by_brand', [
                'label' => __('Shop By Brand'),
                'title' => __('Shop By Brand'),
                'link'  => $this->_url->getUrl('shop-by-brand')
            ]);
            $breadcrumbs->addCrumb('brand_detail', [
                'label' => $brand->getName(),
                'title' => $brand->getName()
            ]);
        }

        return $resultPage;
    }
}
