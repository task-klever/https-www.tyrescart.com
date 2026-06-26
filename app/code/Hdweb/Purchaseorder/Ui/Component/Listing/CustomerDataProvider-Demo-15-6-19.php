<?php
namespace Hdweb\Purchaseorder\Ui\Component\Listing;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class CustomerDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{ 

   
    protected $request;
    protected $attribute;
    

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'purchase_order',
        $resourceModel = 'Hdweb\Purchaseorder\Model\ResourceModel\Purchaseorder\Collection',
         \Magento\Framework\App\Request\Http $request,
         \Magento\Eav\Model\Entity\Attribute $attribute 
    ) {
        $this->request = $request;
        $this->attribute = $attribute;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }


   protected function _initSelect()
   {  

      parent::_initSelect();
      $actionname=$this->request->getFullActionName();
      // $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
      // $_request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
      
      // $attributeCode = 'name';
      // $entityType = 'catalog_product';
      //$attributeInfo = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)->loadByCode($entityType, $attributeCode);
      // $attributeInfo = $this->attribute->loadByCode($entityType, $attributeCode);
      // $attributeId = $attributeInfo->getAttributeId();                          


      // $customer_id=$this->request->getParam('customer_id');
      // if(isset($customer_id) && !empty($customer_id)){
      if($actionname == 'purchaseorder_create_grid'){
          $this->getSelect()
          ->joinLeft(
            ['povendor' => $this->getTable('po_vendor')],
            'main_table.vendor = povendor.id',
            array('povendor.name as vendorname')
          );

           $this->getSelect()
          ->joinLeft(
            ['poitem' => $this->getTable('purchase_order_item')],
            'main_table.id = poitem.poid',
            array('poitem.qty')
          )
          ->group('main_table.id')
          ->columns('SUM(poitem.qty) as poqty');

          //->where("main_table.customer_id= 500")
         // ->where("product.attribute_id IS NULL OR product.attribute_id=". $attributeId);



      // }else{
          //     $this->getSelect()->joinLeft(
          //   ['customer' => $this->getTable('customer_entity')],
          //   'main_table.customer_id = customer.entity_id',
          //    ['name' => "CONCAT(customer.firstname, ' ', customer.lastname)"]
          // )
          // ->joinLeft(
          //   ['product' => $this->getTable('catalog_product_entity_varchar')],
          //   'main_table.product_id = product.entity_id',
          //   array('product.value as productname','attribute_id')
          // )
          //  ->joinLeft(
          //   ['address' => $this->getTable('customer_address_entity')],
          //   'main_table.customer_id = address.parent_id',
          // array('address.company as customercompany')
          // )
          // ->where("product.attribute_id IS NULL OR product.attribute_id=". $attributeId)
          // ->group('main_table.customer_id')
          // ->columns('COUNT(*) as totaldownload ');

    //  }
      
    //  echo $this->getSelect();exit;
     //  $this
     //  ->addFilterToMap(
     //     'name', 
     //     new \Zend_Db_Expr('CONCAT(customer.firstname," ",customer.lastname)')
     //  ); 
     
     $this
     ->addFilterToMap('vendorname', 'povendor.name');  

    $this
     ->addFilterToMap('poqty', 'poitem.qty');

    }
      return $this;
  }
}