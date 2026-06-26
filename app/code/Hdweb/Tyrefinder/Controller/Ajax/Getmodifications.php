<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getmodifications extends \Magento\Framework\App\Action\Action
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
		// $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Getmodifications.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('Getmodifications : start');

        if($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document'){
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setHttpResponseCode(301);
    		return $resultRedirect->setPath('/');
		}

		$response = array();
		$make  = $this->getRequest()->getParam('make');
		$model = $this->getRequest()->getParam('model');
		$year  = $this->getRequest()->getParam('year');

		// Old: External API call to Klever/wheel-size
		// $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $modifications_url = "https://wheel-api.klever.ae/v1/modifications/?user_key=".$wheelApiKey."&make=".$make."&model=".$model."&year=".$year."&region=medm";
        // $modifications = file_get_contents($modifications_url);
        // if(empty($modifications)) {
		// 	$wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
		// 	$modifications_url = "https://api.wheel-size.com/v2/modifications/?user_key=".$wheelApiKey."&make=".$make."&model=".$model."&year=".$year."&region=medm";
		// 	$modifications = file_get_contents($modifications_url);
		// }
        // $modifications = json_decode($modifications);

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getModifications($make, $model, (int)$year);
        $modificationsData = $result['rows'];

		$Engines    = array();
        $engineHtml = "";
		if (count($modificationsData) > 0) {
			foreach ($modificationsData as $key => $modificationsvalue) {
				$trim=array();
				$name                  = $modificationsvalue['fuel'] ?? '';
				$slug                  = $modificationsvalue['modification_slug'] ?? '';
				$trim['name']          = $modificationsvalue['trim'] ?? '';
				$trim['power']         = $modificationsvalue['power_hp'] ?? '';
				$Engines[$name][$slug] = $trim;
			}
		}
		foreach ($Engines as $key => $country) {
			$engineHtml .= "<li class='engine-li'>" . $key . "</li>";
            foreach ($country as $slugkey => $slugvalue) {
                $engineHtml .= '<li class="search"><a href="javascript:void(0)" class="" onclick="getTyreSizes(\'' . $slugkey . '\',\'' . $slugvalue['name'] . '\')" >' . $slugvalue['name'] . '<sup class="lightsup" title="248hp | 185kW | 252PS">'.$slugvalue['power'].'hp</sup></a><span id="autosearch-span" style="display:none">'.$slugvalue['name'].' '. $slugvalue['power'].'hp</span></li>';
            }
			$engineHtml .= "";
        }

		$response['response'] = $engineHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}
