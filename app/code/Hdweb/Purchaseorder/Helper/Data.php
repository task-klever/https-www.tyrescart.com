<?php
/**
 * Copyright © 2015 Brainvire . All rights reserved.
 */
namespace Hdweb\Purchaseorder\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $purchaseorder;

    protected $order;

    protected $state;

    protected $orderCollectionFactory;
    protected $purchaseorderitem;
    protected $pickupstores;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
         \Hdweb\Purchaseorder\Model\Purchaseorder $purchaseorder,
         \Magento\Sales\Model\Order $order,
         \Magento\Framework\App\State $state,
         \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
         \Hdweb\Purchaseorder\Model\Purchaseorderitem $purchaseorderitem,
         \Ecomteck\StoreLocator\Model\Stores $pickupstores

    ) {
        parent::__construct($context);

        $this->purchaseorder = $purchaseorder;

        $this->order = $order;

        $this->state = $state;

        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->purchaseorderitem=$purchaseorderitem;
        $this->pickupstores    = $pickupstores;

    }

    public function savePoGrandTotal($orderreference_no)
    {   
            $purchase_collec=$this->purchaseorder->getCollection()
                             ->addFieldToFilter('orderreference_no',$orderreference_no);

            $totalgrandtotal=0;                 
             foreach ($purchase_collec as $key => $value) {
                    $totalgrandtotal=$totalgrandtotal+$value->getGrandtotal() ;
            }             


            $order_obj=$this->order->loadByIncrementId($orderreference_no);   
            $order_id=$order_obj->getId();

            if($order_id > 0 ){
                $totalgrandtotal=number_format($totalgrandtotal,2);    
                $order = $this->order->load($order_id);
                $order->setPoGrandtotal($totalgrandtotal);
                $marggintotal=(float)$order->getGrandTotal()-(float)$totalgrandtotal;
                $marginper=(((float)$order->getGrandTotal() - (float)$totalgrandtotal) / (float)$order->getGrandTotal() ) * 100;
                $marginper=number_format($marginper,2);
                $order->setPoMargin($marggintotal);
                $order->setPoMarginperc($marginper);
                $order->save();                   
            }

             //    $purchase_collec->getSelect()->columns(['grandtotal' => new \Zend_Db_Expr('SUM(grandtotal)')])->group('orderreference_no');;

             // //   echo $purchase_collec->getSelect()->group('orderreference_no');
             //    echo "<pre>";
             //    print_r($purchase_collec->getData());
             //    exit;
                 

        
    }


    public function createPoAllOrder()
    {       
        $this->state->setAreaCode('frontend'); 
         $collection = $this->orderCollectionFactory->create()->addAttributeToSelect('*');
         foreach ($collection as $key => $value) {

         
                $orderreference_no=$value->getIncrementId();
                    $purchase_collec=$this->purchaseorder->getCollection()
                                     ->addFieldToFilter('orderreference_no',$orderreference_no);

                    $totalgrandtotal=0;                 
                     foreach ($purchase_collec as $key => $value) {
                            $totalgrandtotal=$totalgrandtotal+$value->getGrandtotal() ;
                    }             


                    $order_obj=$this->order->loadByIncrementId($orderreference_no);   
                    $order_id=$order_obj->getId();

                    if($order_id > 0 ){
                        $totalgrandtotal=number_format($totalgrandtotal,2);    
                        $order = $this->order->load($order_id);
                        $order->setPoGrandtotal($totalgrandtotal);
                        $marggintotal=(float)$order->getGrandTotal()-(float)$totalgrandtotal;
                        $marginper=(((float)$order->getGrandTotal() - (float)$totalgrandtotal) / (float)$order->getGrandTotal() ) * 100;
                        $marginper=number_format($marginper,2);
                        $order->setPoMargin($marggintotal);
                        $order->setPoMarginperc($marginper);
                        $order->save();                   
                    }

        }
    }   
    public function validatePurchaseOrder($orderreference_no,$poType)
    {       
        $poitem = $this->purchaseorderitem->getCollection()->addFieldToFilter('order_id',$orderreference_no)
                                                        ->addFieldToFilter('po_type',$poType)
                                                        ->addFieldToFilter('sku',['neq' => 'SEV200200216']);
        $qty = array();
        $totalPoQty = 0;
        if(count($poitem) > 0){
            foreach($poitem as $poItemdata){
            $qty[] = $poItemdata->getQty();
            }
            $totalPoQty = array_sum($qty);
            return $totalPoQty;
        }else{
            return $totalPoQty;
        }
        
    }

    public function getIntallerDetails($installer_id)
    {
        $pickupstoresData = $this->pickupstores->load($installer_id);

        return $pickupstoresData;
    }
    
    public function getPurchaseOrderByOrderNumber($orderreference_no)
    {   
        //$purchaseCollection = $this->purchaseorder->getCollection()->addFieldToFilter('orderreference_no',$orderreference_no);
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('purchase_order'); 
        $sql = $connection->select()
                  ->from($tableName) // to select all fields
                  //->from($tableName, $fields) // to select some particular fields               
                  ->where('orderreference_no = ?', $orderreference_no);
                     
        $result = $connection->fetchAll($sql); 
        return $result;
    }
}