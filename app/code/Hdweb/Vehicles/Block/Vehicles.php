<?php

namespace Hdweb\Vehicles\Block;

class Vehicles extends \Magento\Framework\View\Element\Template
{
    protected $vehiclesHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hdweb\Vehicles\Helper\Data $vehiclesHelper
    ) {
        parent::__construct($context);
        $this->vehiclesHelper = $vehiclesHelper;
    }

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
            'tyres',
            [
                'label' => __('Tyres'),
                'title' => __('Tyres'),
                'link' => $this->_storeManager->getStore()->getBaseUrl() . 'tyres',
            ]
        );
        $breadcrumbs->addCrumb(
            'cars',
            [
                'label' => __('Cars'),
                'title' => __('Cars'),
                'link' => $this->_storeManager->getStore()->getBaseUrl() . 'tyres/cars',
            ]
        );

        $this->pageConfig->getTitle()->set(__('Buy Tyres Online by Vehicle in UAE at Best Prices | TyresCart'));
        // $this->pageConfig->setKeywords(__('buy tyres, vehicle tires, premium tyres, high-performance tyres, luxury car tyres, tyre selection'));
        $this->pageConfig->setDescription(__('Shop tyres online by vehicle at TyresCart. Find tyres for top car brands with fast delivery and expert fitting in Dubai, Abu Dhabi & across UAE.'));

        return parent::_prepareLayout();
    }

    public function getVehiclesMakes()
    {
        return $this->vehiclesHelper->getVehiclesMakes();
    }
}
