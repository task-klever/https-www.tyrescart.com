<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecomteck\StoreLocator\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends Action
{
    /**
     * @var string
     */
    const META_DESCRIPTION_CONFIG_PATH = 'ecomteck_storelocator/seo/meta_description';
    
    /**
     * @var string
     */
    const META_KEYWORDS_CONFIG_PATH = 'ecomteck_storelocator/seo/meta_keywords';
    
    /**
     * @var string
     */
    const META_TITLE_CONFIG_PATH = 'ecomteck_storelocator/seo/meta_title';
    
    /**
     * @var string
     */
    const BREADCRUMBS_CONFIG_PATH = 'ecomteck_storelocator/seo/breadcrumbs';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    
    /** @var \Magento\Framework\View\Result\PageFactory  */
    public $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Load the page defined in view/frontend/layout/storelocator_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $resultRedirectFactory = $objectManager->get('Magento\Framework\Controller\Result\RedirectFactory');
        $params = $this->getRequest()->getParams();
        $quote = $checkoutSession->getQuote();


        $source = $this->getRequest()->getParam('fitment_type');

        if($source == 'installation_at_fitment_center'){

            $quote->setFitmentType('installation_at_fitment_center');

            $quote->save();

        }


        $cartItemsCount = $quote->getItemsCount();
        $fitmentType = $quote->getFitmentType();

        // ref=cart without fitment_type → load refcart page
        if (isset($params['ref']) && $params['ref'] === 'cart' && !isset($params['fitment_type'])) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('storelocator_refcart_index');
            $resultPage->getConfig()->getTitle()->set(__('Select Installer'));
            return $resultPage;
        }

        if($params && isset($params['ref']) && $params['fitment_type']){
            if($cartItemsCount == 0){
                $resultRedirect = $resultRedirectFactory->create();
                $resultRedirect->setPath('storelocator');
                return $resultRedirect;
            }
            if($fitmentType && $fitmentType == 'installation_at_fitment_center' || $fitmentType == 'mobile_van_installation'){
                // show installers
            }elseif($fitmentType && $fitmentType == 'free_delivery_at_home'){
                $resultRedirect = $resultRedirectFactory->create();
                $resultRedirect->setPath('checkout');
                return $resultRedirect;
            }else{
                $resultRedirect = $resultRedirectFactory->create();
                $resultRedirect->setPath('storelocator');
                return $resultRedirect;
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            $this->scopeConfig->getValue(self::META_TITLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setDescription(
            $this->scopeConfig->getValue(self::META_DESCRIPTION_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setKeywords(
            $this->scopeConfig->getValue(self::META_KEYWORDS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
        if ($this->scopeConfig->isSetFlag(self::BREADCRUMBS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
            
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label'    => __('Home'),
                        'link'     => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'storelocator',
                    [
                        'label'    => __('StoreLocator'),
                    ]
                );
            }
        }
        
        return $resultPage;

    }

}
