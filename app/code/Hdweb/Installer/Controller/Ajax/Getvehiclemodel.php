<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hdweb\Installer\Controller\Ajax;

/*use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;*/

class Getvehiclemodel extends \Magento\Framework\App\Action\Action
{

    protected $helper;
    protected $resultJsonFactory;
    protected $cartModel;
    protected $customerRepository;
    // protected $finderhelper;
    protected $_cartModel;
    protected $_customerRepository;
    protected $flatWheelData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        // \Hdweb\Tyrefinder\Helper\Data $finderhelper
        \Klever\VehicleTyresGuide\Model\ResourceModel\FlatWheelData $flatWheelData
    ) {
        parent::__construct($context);
        $this->helper              = $helper;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->_cartModel          = $cartModel;
        $this->_customerRepository = $customerRepository;
        // $this->finderhelper        = $finderhelper;
        $this->flatWheelData       = $flatWheelData;
    }

    public function execute()
    {

        $vehiclemakes = $this->helper->jsonDecode($this->getRequest()->getContent());
        if ($vehiclemakes) {
            try {
                // Old: External API call to Klever/wheel-size
                // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
				// $VehicleModel_url = "https://wheel-api.klever.ae/v1/models/?user_key=".$wheelApiKey."&make=".$vehiclemakes. "&region=medm";
				// $WheelVehicleModel = file_get_contents($VehicleModel_url);
                // if(empty($WheelVehicleModel)){
                //     $VehicleModel_url = "https://api.wheel-size.com/v2/models/?user_key=".$wheelApiKey."&make=".$vehiclemakes. "&region=medm";
                //     $WheelVehicleModel = file_get_contents($VehicleModel_url);
                // }
				// $WheelVehicleModel = json_decode($WheelVehicleModel);

                // New: Read from Klever VehicleTyresGuide flat_wheel_data table
                $result = $this->flatWheelData->getModels($vehiclemakes, ['ordering' => 'name']);

                $modelSelectHtml   = '<option value="">' . __('Select Model') . '</option>';
                $yearSelectHtml    = '<option value="">' . __('Select Year') . '</option>';

				if (count($result['rows']) > 0) {
					foreach ($result['rows'] as $modelOption) {
						$modelSelectHtml .= '<option value="' . $modelOption['slug'] . '">' . $modelOption['name'] . '</option>';
					}
				}
                $response[] = [
                    'vehiclemodel' => $modelSelectHtml,
                    'vehicleyear'  => $yearSelectHtml,
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);

            } catch (Exception $ex) {

            }
        }
    }
}
