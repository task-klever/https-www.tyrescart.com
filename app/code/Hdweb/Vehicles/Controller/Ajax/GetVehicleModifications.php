<?php
namespace Hdweb\Vehicles\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class GetVehicleModifications extends \Magento\Framework\App\Action\Action
{
	protected $_resource;
	protected $resultJsonFactory;
	protected $vehiclesHelper;
	// protected $scopeConfig;
	protected $flatWheelData;

    public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Hdweb\Vehicles\Helper\Data $vehiclesHelper,
		// ScopeConfigInterface $scopeConfig
		FlatWheelData $flatWheelData
	) {
		$this->resultJsonFactory = $resultJsonFactory;
		$this->_resource = $resource;
		$this->vehiclesHelper 		= $vehiclesHelper;
		// $this->scopeConfig = $scopeConfig;
		$this->flatWheelData = $flatWheelData;
    	parent::__construct($context);
    }

    public function execute()
    {
		$response = array();
		$make  = $this->getRequest()->getParam('make');
		$model = $this->getRequest()->getParam('model');
		$year  = $this->getRequest()->getParam('year');

		// Old: External API call to Klever/wheel-size
		// $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
		// $modifications_url = "https://wheel-api.klever.ae/v1/modifications/?user_key=".$wheelApiKey."&make=".$make."&model=".$model."&year=".$year."&region=medm";
        // $modifications = file_get_contents($modifications_url);
        // if(empty($modifications)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $modifications_url = "https://api.wheel-size.com/v2/modifications/?user_key=".$wheelApiKey."&make=".$make."&model=".$model."&year=".$year."&region=medm";
        //     $this->vehiclesHelper->logApiAccess($modifications_url);
        //     $modifications = file_get_contents($modifications_url);
        // }
        // $modifications = json_decode($modifications);
        // $Engines    = array();
        // $engineHtml = "";
        // if (count($modifications->data) > 0) {
		// 	foreach ($modifications->data as $key => $modificationsvalue) {
		// 		$trim=array();
		// 		$name                  = $modificationsvalue->engine->fuel;
		// 		$slug                  = $modificationsvalue->slug;
		// 		$trim['name']          = $modificationsvalue->trim;
		// 		$trim['power']         = $modificationsvalue->engine->power->hp;
		// 		$Engines[$name][$slug] = $trim;
		// 	}
		// }

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
            $engineHtml .= "<div class='w-full engine-type'><div class='text-center text-[0.9rem] xl:text-[1.1rem] text-theme-dark font-semibold [direction:ltr]'>" . $key . "</div></div>";
			foreach ($country as $slugkey => $slugvalue) {
				$var = 'getVehicleTyreSizes(\'' . $slugkey . '\',\'' . $slugvalue['name'] . '\',\'' . $make . '\',\'' . $model . '\',\'' . $year . '\')';
				$engineHtml .= '<a class="group cursor-pointer relative rounded-[10px] border transition-all duration-200 min-w-[107px] md:min-w-[116px] border-gray-200 bg-white text-gray-800 hover:border-theme-blue hover:shadow-sm text-center px-4 py-3">
									<span class="block text-sm font-medium [direction:ltr]" title="'.$slugvalue['name'].'" onclick="'.$var.'">
                    						<span>'.$slugvalue['name'].'</span>
                					</span>
              				    </a>';
			}
		}

		$response['response'] = $engineHtml;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }
}