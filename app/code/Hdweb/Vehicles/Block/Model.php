<?php

namespace Hdweb\Vehicles\Block;

class Model extends \Magento\Framework\View\Element\Template
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

        $path = trim($this->getRequest()->getPathInfo(), '/'); // e.g. "tyres/cars/suzuki/baleno"
        $parts = explode('/', $path);
        
        $make = $parts[2] ?? null;  // suzuki (index 2 because tyres/cars/{make})
        $model = $parts[3] ?? null; // baleno

        $vehicleData = [];

        if ($make && $model) {
            $strModel = str_replace('-', ' ', $make);
            $makeString = ucwords($strModel);
            $strModel = str_replace('-', ' ', $model);
            $modelString = ucwords($strModel);

            $breadcrumbs->addCrumb(
                $make,
                [
                    'label' => __($makeString),
                    'title' => __($makeString),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . 'tyres/cars/' . $make,
                ]
            );
            $breadcrumbs->addCrumb(
                $model,
                [
                    'label' => __($modelString),
                    'title' => __($modelString),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . 'tyres/cars/' . $make . '/' . $model,
                ]
            );
            $vehicleData = $this->getVehicleData();
        }

        if ($vehicleData) {

            if ($vehicleData['meta_title']) {
                $metaTitle = $vehicleData['meta_title'];
                $this->pageConfig->getTitle()->set(__($metaTitle));
            } else {
                $this->setDefaultMetaTitle($makeString, $modelString);
            }

            if ($vehicleData['meta_keywords']) {
                $metaKeywords = $vehicleData['meta_keywords'];
                $this->pageConfig->setKeywords(__($metaKeywords));
            } else {
                $this->setDefaultMetaKeywords($makeString, $modelString);
            }

            if ($vehicleData['meta_description']) {
                $metaDescription = $vehicleData['meta_description'];
                $this->pageConfig->setDescription(__($metaDescription));
            } else {
                $this->setDefaultMetaDescription($makeString, $modelString);
            }
        } else {
            $this->setDefaultMetaTitle($makeString, $modelString);
            $this->setDefaultMetaKeywords($makeString, $modelString);
            $this->setDefaultMetaDescription($makeString, $modelString);
        }

        return parent::_prepareLayout();
    }

    public function getVehicleYears()
    {
        $make = $this->getRequest()->getParam('make');
        $model = $this->getRequest()->getParam('model');
        $vehicleModels = $this->vehiclesHelper->getVehicleYears($make, $model);
        return $vehicleModels;
    }

    public function getVehicleData()
    {
        $make = $this->getRequest()->getParam('make');
        $model = $this->getRequest()->getParam('model');
        $vehicleData = $this->vehiclesHelper->getVehicleDataFromMakeModel($make, $model);
        return $vehicleData;
    }

    public function getCarImage()
    {
        $make = $this->getRequest()->getParam('make');
        $model = $this->getRequest()->getParam('model');
        $carImage = $this->vehiclesHelper->getCarImageFromMakeModel($make, $model);
        return $carImage;
    }

    public function getVehiclesSearchbyFormUrl()
    {
        return $this->vehiclesHelper->getVehiclesSearchbyFormUrl();
    }

    public function getModelParagraph1Default()
    {
        return $this->vehiclesHelper->getModelParagraph1Default();
    }

    public function getModelParagraph2Default()
    {
        return $this->vehiclesHelper->getModelParagraph2Default();
    }

    public function getMediaUrl()
    {
        return $this->vehiclesHelper->getMediaUrl();
    }

    public function setDefaultMetaTitle($makeString, $modelString)
    {
        if ($this->vehiclesHelper->getDefaultMetaTitleForModelPage()) {
            $defaultMetaTitle = $this->vehiclesHelper->getDefaultMetaTitleForModelPage();
            $replacements = [
                '{make}' => $makeString,
                '{model}' => $modelString
            ];
            $defaultMetaTitle = str_replace(array_keys($replacements), array_values($replacements), $defaultMetaTitle);
            $this->pageConfig->getTitle()->set(__($defaultMetaTitle));
        } else {
            $this->pageConfig->getTitle()->set(__('Buy ' . $makeString));
        }
    }

    public function setDefaultMetaKeywords($makeString, $modelString)
    {
        if ($this->vehiclesHelper->getDefaultMetaKeywordsForModelPage()) {
            $defaultMetaKeywords = $this->vehiclesHelper->getDefaultMetaKeywordsForModelPage();
            $replacements = [
                '{make}' => $makeString,
                '{model}' => $modelString
            ];
            $defaultMetaKeywords = str_replace(array_keys($replacements), array_values($replacements), $defaultMetaKeywords);
            $this->pageConfig->setKeywords(__($defaultMetaKeywords));
        } else {
            $this->pageConfig->setKeywords(__('' . $makeString . ', car tires, '));
        }
    }

    public function setDefaultMetaDescription($makeString, $modelString)
    {
        if ($this->vehiclesHelper->getDefaultMetaDescriptionForModelPage()) {
            $defaultMetaDescription = $this->vehiclesHelper->getDefaultMetaDescriptionForModelPage();
            $replacements = [
                '{make}' => $makeString,
                '{model}' => $modelString
            ];
            $defaultMetaDescription = str_replace(array_keys($replacements), array_values($replacements), $defaultMetaDescription);
            $this->pageConfig->setDescription(__($defaultMetaDescription));
        } else {
            $this->pageConfig->setDescription(__('Shop premium ' . $makeString));
        }
    }
}
