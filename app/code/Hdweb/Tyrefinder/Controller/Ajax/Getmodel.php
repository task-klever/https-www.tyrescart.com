<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getmodel extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
    // protected $scopeConfig;
    protected $flatWheelData;

    public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        // ScopeConfigInterface $scopeConfig
        FlatWheelData $flatWheelData
	) {
		$this->resultJsonFactory = $resultJsonFactory;
        // $this->scopeConfig = $scopeConfig;
        $this->flatWheelData = $flatWheelData;
    	parent::__construct($context);
    }

    public function execute()
    {
		// $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Getmodel.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('Getmodel : start');

        if($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document'){
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setHttpResponseCode(301);
    		return $resultRedirect->setPath('/');
		}

		$response = array();
		$make = $this->getRequest()->getParam('make');

		// Old: External API call to Klever/wheel-size
		// $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $VehicleModel_url = "https://wheel-api.klever.ae/v1/models/?user_key=".$wheelApiKey."&make=".$make. "&region=medm";
        // $WheelVehicleModel = file_get_contents($VehicleModel_url);
        // if(empty($WheelVehicleModel)) {
		// 	$wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
		// 	$VehicleModel_url = "https://api.wheel-size.com/v2/models/?user_key=".$wheelApiKey."&make=".$make. "&region=medm";
		// 	$WheelVehicleModel = file_get_contents( $VehicleModel_url);
		// }
        // $WheelVehicleModel = json_decode($WheelVehicleModel);

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getModels($make, ['ordering' => 'name']);
        $modelsData = $result['rows'];

        $selectHtml='';
        if (count($modelsData) > 0) {
			foreach ($modelsData as $modelOption) {
				$slug = $modelOption['slug'];
				$name = $modelOption['name'];
				$var = "getyear('$slug' , '$name')";
				$selectHtml .= '<li class="search"><a href="javascript:void(0)" class="" title="'.$name.'" onclick="'.$var.'"><span class="">'.$name.'</span></a></li>';
			}
		}

		$response['response'] = $selectHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}
