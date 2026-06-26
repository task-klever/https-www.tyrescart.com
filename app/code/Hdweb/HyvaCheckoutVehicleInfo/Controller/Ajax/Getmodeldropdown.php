<?php

namespace Hdweb\HyvaCheckoutVehicleInfo\Controller\Ajax;

// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Magento\Store\Model\ScopeInterface;
use Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData;

class Getmodeldropdown extends \Magento\Framework\App\Action\Action
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
        $make = $this->getRequest()->getParam('make');

        if (!$make) {
            $response['response'] = 'error';
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
        $quote = $this->checkoutSession->getQuote();
        $quote->setMake($make);
        $quote->save();

        // Old: External API call to Klever/wheel-size
        // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
        // $VehicleModel_url = "https://wheel-api.klever.ae/v1/models/?user_key=" . $wheelApiKey . "&make=" . $make . "&region=medm";
        // $WheelVehicleModel = file_get_contents($VehicleModel_url);
        // if(empty($WheelVehicleModel)) {
        //     $wheelApiKey = $this->scopeConfig->getValue('hdwebapi/general/wheelsize_api_key', ScopeInterface::SCOPE_STORE);
        //     $VehicleModel_url = "https://api.wheel-size.com/v2/models/?user_key=" . $wheelApiKey . "&make=" . $make . "&region=medm";
        //     $this->dataHelper->logApiAccess($VehicleModel_url);
        //     $WheelVehicleModel = file_get_contents($VehicleModel_url);
        // }
        // $WheelVehicleModel = json_decode($WheelVehicleModel);

        // New: Read from Klever VehicleTyresGuide flat_wheel_data table
        $result = $this->flatWheelData->getModels($make, ['ordering' => 'name']);

        $selectHtml = '<option value="">' . __('Select Model') . '</option>';
        if (count($result['rows']) > 0) {
            foreach ($result['rows'] as $modelOption) {
                $selectHtml .= '<option value=' . $modelOption['slug'] . ' data-model-val=' . $modelOption['name'] . ' data-model-slug=' . $modelOption['slug'] . '>' . $modelOption['name'] . '</option>';
            }
        }

        $response['response'] = $selectHtml;
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
