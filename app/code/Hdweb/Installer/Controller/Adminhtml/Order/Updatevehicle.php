<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hdweb\Installer\Controller\Adminhtml\Order;
use \Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\ResultFactory; 

/*use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;*/

class Updatevehicle extends \Magento\Framework\App\Action\Action {

    protected $helper;
    protected $_orderRepository;
    protected $_messageManager;
    protected $request;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, 
    \Magento\Framework\Json\Helper\Data $helper, 
    \Magento\Framework\App\RequestInterface $request,
    OrderRepositoryInterface $orderRepository,
    \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->request = $request;
        $this->_orderRepository = $orderRepository;
        $this->_messageManager = $messageManager;
    }

    public function execute() {

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $new = 0;
        $vehicleDetails = array();
        $vehicleDetails['vechicle_data'] = array('Plate' => $this->request->getParam('plate_number'), 'Make' => $this->request->getParam('vehiclelist1'), 'Year' => $this->request->getParam('vehicleyear1'), 'Model' => $this->request->getParam('vehiclemodel1'),'Vin' => $this->request->getParam('vin_number'));
        $finalVehicleData = serialize($vehicleDetails);
        // print_r($finalVehicleData);
        $order = $this->_orderRepository->get($this->request->getParam('order_id'));
        if ($order->getVehicleDetails()) {
            $new = 1;
        }
        $data = $this->request->getParams();
      
        if($data['plate_number']){
            $plate = $data['plate_number'];
        } else {
            $plate = '';
        }
        if(isset($data['vehiclelist1'])){
            if($data['vehiclelist1'] || $data['vehiclelist1'] && $data['vehiclelist1_text']){
                $make = $data['vehiclelist1'];
            } else{
                $make = $data['vehiclelist1_text'];
            }
        } else {
            $make = $data['vehiclelist1_text'];
        }
        if(isset($data['vehiclemodel1'])){
            if($data['vehiclemodel1'] || $data['vehiclemodel1'] && $data['vehiclemodel1_text']){
                $model = $data['vehiclemodel1'];
            } else{
                $model = $data['vehiclemodel1_text'];
            }
        } else {
            $model = $data['vehiclemodel1_text'];
        }
        if(isset($data['vehicleyear1'])){
            if($data['vehicleyear1'] || $data['vehicleyear1'] && $data['vehicleyear1_text']){
                $year = $data['vehicleyear1'];
            } else{
                $year = $data['vehicleyear1_text'];
            }
        } else {
            $year = $data['vehicleyear1_text'];
        }
        $order->setVehicleDetails($finalVehicleData)
              ->setMake($make)
              ->setModel($model)
              ->setYear($year)
              ->setPlate($plate);
        $order->save();
        if ($new = 0) {
            $this->_messageManager->addSuccessMessage('Vehicle Added Successfully');
        }else{
            $this->_messageManager->addSuccessMessage('Vehicle Updated Successfully');
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;

    }

}
