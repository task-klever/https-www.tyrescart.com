<?php
namespace Hdweb\HyvaCheckoutVehicleInfo\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getyeardropdown extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
	// protected $scopeConfig;

	protected $checkoutSession;
	protected $dataHelper;
	protected $flatWheelData;

    public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        // ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Hdweb\Vehicles\Helper\Data $dataHelper,
		FlatWheelData $flatWheelData
	) {
		$this->resultJsonFactory = $resultJsonFactory;
		// $this->scopeConfig = $scopeConfig;
		$this->checkoutSession = $checkoutSession;
		$this->dataHelper = $dataHelper;
		$this->flatWheelData = $flatWheelData;
    	parent::__construct($context);
    }

    public function execute()
    {
		$response = array();
		$make  = $this->getRequest()->getParam('make');
		$model = $this->getRequest()->getParam('model');
		$quote = $this->checkoutSession->getQuote();
		$quote->setModel($model);
		$quote->save();

		// Old: External API call to Klever/wheel-size
		// $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
		// $modelyear_url = "https://wheel-api.klever.ae/v1/years/?user_key=".$wheelApiKey."&make=".$make."&model=".$model. "&region=medm";
        // $modelyear = file_get_contents($modelyear_url);
        // if(empty($modelyear)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $modelyear_url = "https://api.wheel-size.com/v2/years/?user_key=".$wheelApiKey."&make=".$make."&model=".$model. "&region=medm";
        //     $this->dataHelper->logApiAccess($modelyear_url);
        //     $modelyear = file_get_contents($modelyear_url);
        // }
        // $modelyear = json_decode($modelyear);

		// New: Read from Klever VehicleTyresGuide flat_wheel_data table
		$result = $this->flatWheelData->getYears($make, $model);

		$selectHtml = '<option value="">' . __('Select Year') . '</option>';
		if (count($result['years']) > 0) {
			foreach ($result['years'] as $year) {
                $selectHtml .= '<option value=' . $year . ' data-year-val='.$year.' data-year-slug='.$year.'>' . $year . '</option>';
			}
		}

		$response['response'] = $selectHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}