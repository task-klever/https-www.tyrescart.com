<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

// use Magento\Framework\View\Asset\Repository as AssetRepository;
// use Magento\Framework\Filesystem\Io\File;
// use Magento\Framework\Module\Dir\Reader;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class GetAllBrands extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    // protected $file;
    // protected $moduleDirReader;
    protected $flatWheelData;
    protected $assetRepo;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        // File $file,
        // Reader $moduleDirReader
        FlatWheelData $flatWheelData,
        AssetRepository $assetRepo
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        // $this->file = $file;
        // $this->moduleDirReader = $moduleDirReader;
        $this->flatWheelData = $flatWheelData;
        $this->assetRepo = $assetRepo;
        parent::__construct($context);
    }

    public function execute()
    {
        if($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document'){
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setHttpResponseCode(301);
    		return $resultRedirect->setPath('/');
		}

        $response          = array();
        $selectHtml = "";

        // Old: Read from local JSON file
        // $moduleDir = $this->moduleDirReader->getModuleDir('', 'Hdweb_Tyrefinder');
        // $filePath = $moduleDir . '/view/frontend/web/data/makes.json';
        // $jsonData = $this->file->read($filePath);
        // $vehicleMakes = json_decode($jsonData, true);
        // $vehicleMakesDataArray = $vehicleMakes['data'];

        // New: Read from Klever VehicleTyresGuide GraphQL flat_wheel_data table
        $result = $this->flatWheelData->getMakes(['ordering' => 'name']);
        $vehicleMakesDataArray = $result['rows'];

        foreach ($vehicleMakesDataArray as $vehicle) {
            $vehicleSlug = $vehicle['slug'];
            $vehicleName = $vehicle['name'];
            $var = "getmodel('$vehicleSlug' , '$vehicleName')";

            $imageUrl = $this->assetRepo->getUrl("Hdweb_Vehicles::images/logo/" . $vehicleSlug . ".png");

            $selectHtml .= '<li class="search">
								<div onclick="' . $var . '">
                                    <a href="javascript:void(0)" class="block text-center" title="' . $vehicleName . '" id ="make-' . $vehicleSlug . '">
                                    <div class="flex items-center justify-center mx-auto min-h-[65px]"><img src="' . $imageUrl . '" class="max-w-[60px] object-contain" alt="' . $vehicleName . '" /></div>
                                    <span class="block text-sm font-medium text-gray-700 mt-1 truncate">' . $vehicleName . '</span>
                                    </a>
							    </div>
							</li>';
        }
        $response['response'] = $selectHtml;
        $resultJson           = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
