<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hdweb\Installer\Controller\Ajax;

/*use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;*/

class Getvehicleyear extends \Magento\Framework\App\Action\Action
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
        $vehiclemodel = $this->helper->jsonDecode($this->getRequest()->getContent());

        $make         = $vehiclemodel['make'];
        $vehiclemodel = $vehiclemodel['model'];

        if ($vehiclemodel) {

            // Old: External API call to Klever/wheel-size
            // $wheelApiKey = '49431246078dcbff7a9d65b50471413c4114304a90672922de02014ba0ce9eb9';
            // $modelyear_url = "https://wheel-api.klever.ae/v1/years/?user_key=".$wheelApiKey."&make=".$make."&model=".$vehiclemodel. "&region=medm";
            // $modelyear = file_get_contents($modelyear_url);
            // if(empty($modelyear)) {
            //     $modelyear_url = "https://api.wheel-size.com/v2/years/?user_key=".$wheelApiKey."&make=".$make."&model=".$vehiclemodel. "&region=medm";
            //     $modelyear = file_get_contents($modelyear_url);
            // }
            // $modelyear = json_decode($modelyear);

            // New: Read from Klever VehicleTyresGuide flat_wheel_data table
            $result = $this->flatWheelData->getYears($make, $vehiclemodel);

            $yearSelectHtml    = '<option value="">' . __('Select Year') . '</option>';

			if (count($result['years']) > 0) {
				foreach ($result['years'] as $year) {
					$yearSelectHtml .= '<option value="' . $year . '">' . $year . '</option>';
				}
			}

            $response[] = [
                'vehicleyear' => $yearSelectHtml,
            ];

            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);

        }
    }
}
