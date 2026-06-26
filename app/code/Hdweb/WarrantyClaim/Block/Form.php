<?php
namespace Hdweb\WarrantyClaim\Block;

use Magento\Framework\View\Element\Template;

class Form extends Template
{

    public function _prepareLayout()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_storeManager->getStore()->getBaseUrl(),
            ]
        );
        $breadcrumbs->addCrumb(
            'WarrantyClaim',
            [
                'label' => __('Warranty Claim'),
                'title' => __('Warranty Claim'),
                'link' => $this->_storeManager->getStore()->getBaseUrl() . 'warrantyclaim',
            ]
          
        );

        $this->pageConfig->getTitle()->set(__('Warranty Claim '));
       
        $this->pageConfig->setDescription(__('Warranty Claim '));

        return parent::_prepareLayout();
    }

    public function getFormAction()
    {
        return $this->getUrl('warrantyclaim/index/submit', ['_secure' => true]);
    }
}