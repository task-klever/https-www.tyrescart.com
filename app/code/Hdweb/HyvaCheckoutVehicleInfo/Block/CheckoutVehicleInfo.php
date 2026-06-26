<?php

namespace Hdweb\HyvaCheckoutVehicleInfo\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;

class CheckoutVehicleInfo extends \Magento\Framework\View\Element\Template
{
    protected $scopeConfig;

    protected $file;

    protected $moduleDirReader;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        File $file,
        Reader $moduleDirReader
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->file = $file;
        $this->moduleDirReader = $moduleDirReader;
        parent::__construct($context);
    }

    public function getMakes()
    {
        

        $moduleDir = $this->moduleDirReader->getModuleDir('', 'Hdweb_HyvaCheckoutVehicleInfo'); // Get the module directory
        $filePath = $moduleDir . '/view/frontend/web/data/makes.json'; // Get the file path
        $jsonData = $this->file->read($filePath);
        $vehicleMakes = json_decode($jsonData, true);
        $vehicleMakesDataArray = $vehicleMakes['data'];

        $makes = [];
        foreach ($vehicleMakesDataArray as $vehicle) {
            $makes[] = ['value' => $vehicle['slug'], 'label' => $vehicle['name']];
        }
        return $makes;
    }
}
