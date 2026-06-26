<?php
namespace Hdweb\Tyrefinder\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getyear extends \Magento\Framework\App\Action\Action
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
		// $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Getyear.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('Getyear : start');

        if($_SERVER['HTTP_SEC_FETCH_DEST'] == 'document'){
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setHttpResponseCode(301);
    		return $resultRedirect->setPath('/');
		}

		$response = array();
		$make  = $this->getRequest()->getParam('make');
		$model = $this->getRequest()->getParam('model');

		// Old: External API call to Klever/wheel-size
		// $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $modelyear_url = "https://wheel-api.klever.ae/v1/years/?user_key=".$wheelApiKey."&make=".$make."&model=".$model. "&region=medm";
        // $modelyear = file_get_contents($modelyear_url);
        // if(empty($modelyear)) {
		// 	$wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
		// 	$modelyear_url = "https://api.wheel-size.com/v2/years/?user_key=".$wheelApiKey."&make=".$make."&model=".$model. "&region=medm";
		// 	$modelyear = file_get_contents($modelyear_url);
		// }
        // $modelyear = json_decode($modelyear);

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getYears($make, $model);
        $yearsData = $result['years'];

		$selectHtml='';
		if (count($yearsData) > 0) {
			foreach ($yearsData as $year) {
				$var = "getmodifications($year , $year)";
				$selectHtml .= '<li class="search"><a href="javascript:void(0)" class="" title="'.$year.'" onclick="'.$var.'"><span class="">'.$year.'</span></a></li>';
			}
		}

		$response['response'] = $selectHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}
