<?php

namespace Hdweb\Vehicles\Block;

class Make extends \Magento\Framework\View\Element\Template
{
    protected $vehiclesHelper;

    protected $storeManager;

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

        $make = $this->getRequest()->getParam('make');
        $makeString = '';
        $vehicleData = null;
        if ($make) {
            $strMake = str_replace('-', ' ', $make);
            $makeString = ucwords($strMake);

            $breadcrumbs->addCrumb(
                $make,
                [
                    'label' => __($makeString),
                    'title' => __($makeString),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . 'tyres/cars/' . $make,
                ]
            );
            $vehicleData = $this->getVehicleData();
        }

        if ($vehicleData) {

            if ($vehicleData['meta_title']) {
                $metaTitle = $vehicleData['meta_title'];
                $this->pageConfig->getTitle()->set(__($metaTitle));
                /* echo '<pre>';
                print_r($metaTitle);
                die((__FILE__) . '-->' . (__FUNCTION__) . '--Line(' . (__LINE__) . ')'); */
            } else {
                $this->setDefaultMetaTitle($makeString);
            }

            if ($vehicleData['meta_keywords']) {
                $metaKeywords = $vehicleData['meta_keywords'];
                $this->pageConfig->setKeywords(__($metaKeywords));
            } else {
                $this->setDefaultMetaKeywords($makeString);
            }

            if ($vehicleData['meta_description']) {
                $metaDescription = $vehicleData['meta_description'];
                $this->pageConfig->setDescription(__($metaDescription));
            } else {
                $this->setDefaultMetaDescription($makeString);
            }
        } else {
            $this->setDefaultMetaTitle($makeString);
            $this->setDefaultMetaKeywords($makeString);
            $this->setDefaultMetaDescription($makeString);
        }

        return parent::_prepareLayout();
    }

    public function getVehicleModels()
    {
        $make = $this->getRequest()->getParam('make');
        $vehicleModels = $this->vehiclesHelper->getVehicleModels($make);
        return $vehicleModels;
    }

    public function getVehicleData()
    {
        $make = $this->getRequest()->getParam('make');
        $vehicleData = $this->vehiclesHelper->getVehicleDataFromMake($make);
        return $vehicleData;
    }

    public function getMediaUrl()
    {
        return $this->vehiclesHelper->getMediaUrl();
    }

    public function getMakeParagraph1Default()
    {
        return $this->vehiclesHelper->getMakeParagraph1Default();
    }

    public function getMakeParagraph2Default()
    {
        return $this->vehiclesHelper->getMakeParagraph2Default();
    }

    public function setDefaultMetaTitle($makeString)
    {
        if ($this->vehiclesHelper->getDefaultMetaTitleForMakePage()) {
            $defaultMetaTitle = $this->vehiclesHelper->getDefaultMetaTitleForMakePage();
            $defaultMetaTitle = str_replace('{make}', $makeString, $defaultMetaTitle);
            $this->pageConfig->getTitle()->set(__($defaultMetaTitle));
        } else {
            $this->pageConfig->getTitle()->set(__('Buy ' . $makeString));
        }
    }

    public function setDefaultMetaKeywords($makeString)
    {
        if ($this->vehiclesHelper->getDefaultMetaKeywordsForMakePage()) {
            $defaultMetaKeywords = $this->vehiclesHelper->getDefaultMetaKeywordsForMakePage();
            $defaultMetaKeywords = str_replace('{make}', $makeString, $defaultMetaKeywords);
            $this->pageConfig->setKeywords(__($defaultMetaKeywords));
        } else {
            $this->pageConfig->setKeywords(__('' . $makeString . ', car tires, '));
        }
    }

    public function setDefaultMetaDescription($makeString)
    {
        if ($this->vehiclesHelper->getDefaultMetaDescriptionForMakePage()) {
            $defaultMetaDescription = $this->vehiclesHelper->getDefaultMetaDescriptionForMakePage();
            $defaultMetaDescription = str_replace('{make}', $makeString, $defaultMetaDescription);
            $this->pageConfig->setDescription(__($defaultMetaDescription));
        } else {
            $this->pageConfig->setDescription(__('Shop premium 1' . $makeString));
        }
    }
}
