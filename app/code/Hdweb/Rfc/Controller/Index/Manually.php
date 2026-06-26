<?php

namespace Hdweb\Rfc\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

class Manually extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $stockupdate;
    protected $_logger;
    protected $scopeConfig;
    protected $_timezoneInterface;
    protected $_resouceConnection;
    protected $rfcCollection;
    protected $productCollectionFactory;
    protected $_filesystem;
    protected $_storeManager;
    protected $_indexerFactory;
    protected $_indexerCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Hdweb\Rfc\Cron\Stockupdate $stockupdate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\App\ResourceConnection $resouceConnection,
        \Hdweb\Rfc\Model\ResourceModel\Rfc\Collection $rfcCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Filesystem $_filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
    ) {
        $this->resultPageFactory         = $resultPageFactory;
        $this->stockupdate               = $stockupdate;
        $this->_logger                   = $logger;
        $this->scopeConfig               = $scopeConfig;
        $this->_timezoneInterface        = $timezoneInterface;
        $this->_resouceConnection        = $resouceConnection;
        $this->rfcCollection             = $rfcCollection;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->_filesystem               = $_filesystem;
        $this->_storeManager             = $storeManager;
        $this->_indexerFactory           = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $rfcEnable = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_enable', $storeScope);

        if ($rfcEnable == 1) {

            $rfcUrl         = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_rfc_url', $storeScope);
            $rfcUsername    = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_rfc_username', $storeScope);
            $rfcPassword    = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_rfc_password', $storeScope);
            $rfcFunction    = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_rfc_function', $storeScope);
            $rfcEnableEmail = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_enable_email', $storeScope);
            $rfcEmailids    = $this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_emailids', $storeScope);

            $parts = explode(",", $rfcEmailids);
            $to    = implode(', ', $parts);

            $ipaddress = $this->getIpAddress();
            echo '<pre>';
            try {
                $dbuser = $rfcUsername;
                $dbpass = $rfcPassword;
                $dbhost = $rfcUrl;
                $dbname = $rfcFunction;
        
                // Corrected connection string
                $conn = new \PDO("sqlsrv:Server=$dbhost;Database=$dbname", $dbuser, $dbpass);
            } catch (\PDOException $e) {
                echo "Error : " . $e->getMessage() . "<br/>";
                die($e);
            }
        
			//$conn = $this->_resouceConnection->getConnection('costom_api');
			$expectedCount = 4000;
            $query = "SELECT * FROM [gcccoastmsdb].[dbo].[WITMAST]";
            $query = "SELECT * FROM [gcccoastmsdb].[dbo].[WITMAST] where ITMODEL >=2021";
           /* $data = $conn->fetchAll($query);
            print_r($data);
            exit;*/

            $stmt  = $conn->prepare($query);
            $stmt->execute();
            $response = $stmt->fetchAll(\PDO::FETCH_ASSOC);
          /*  foreach ($response as $key=>$value){
                echo "</br>";
                print_r($value); exit;
            }die('not found');*/
            
            //echo "<pre>";print_r($response);die;

			$actualCount = count($response);
			$currentDate = date("Y-m-d H:i:s");
			$date = new \DateTime($currentDate.' +00'); 
			$date->setTimezone(new \DateTimeZone('Asia/Dubai'));
			$currentDate = $date->format('Y-m-d H:i:s');
			$actualDate = $response[0]['WTRFDATE'];
			$subject = '';
			$dateDifference = $this->getDateDifference($actualDate, $currentDate);
			$days = $dateDifference['days'];
			$hours = $dateDifference['hours'];
			$proceed = 0;
			if($actualCount < $expectedCount){
				$proceed = 0;
			}else{
				if($days == 0 && $hours == 0){
					$proceed = 1;
				}else{
					$proceed = 0;
				}
			}
			$proceed = 1;
            // echo "<pre>";
            //  print_r($response);die;

            if ($this->scopeConfig->getValue('rfc_section/product_stock_rfc_group/product_stock_debugmode', $storeScope) == 1) {
                $date   = $this->getTodaysDate();
                /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/RFC-' . $rfcFunction . '.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info($date);
                $logger->info($rfcUrl);
                $logger->info($response);
                $logger->info('-------------------------------------------------------------------------');*/

                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RFC-' . $rfcFunction . '.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info($date);
                $logger->info($rfcUrl);
                $logger->info($response);
                $logger->info('-------------------------------------------------------------------------');
            }

            $connection                       = $this->_resouceConnection->getConnection();
            $eavEntityTypeTable               = $this->_resouceConnection->getTableName('eav_entity_type');
            $eavAttributeTable                = $this->_resouceConnection->getTableName('eav_attribute');
            $eavAttributeOptionTable          = $this->_resouceConnection->getTableName('eav_attribute_option');
            $eavAttributeOptionValueTable     = $this->_resouceConnection->getTableName('eav_attribute_option_value');
            $catalogProductEntityVarcharTable = $this->_resouceConnection->getTableName('catalog_product_entity_varchar');
            $catalogProductEntityIntTable     = $this->_resouceConnection->getTableName('catalog_product_entity_int');
            $cataloginventoryStockItemTable   = $this->_resouceConnection->getTableName('cataloginventory_stock_item');
            $catalogPriceIndexTable           = $this->_resouceConnection->getTableName('catalog_product_index_price');
            $catalogPriceDecimalTable         = $this->_resouceConnection->getTableName('catalog_product_entity_decimal');
            $priceAttrId = 77; // price attribute id
            $statusAttrId = 97; // status attribute id

            $manually = 1;
            if ($manually == 1) {
                $method = 'Manually';
            } else {
                $method = 'Auto';
            }

            $totalcount   = 0;
            $successcount = 0;
            $failedcount  = 0;
            $rfc          = $this->_objectManager->create('Hdweb\Rfc\Model\Rfc');
            $rfc->setData('rfc_name', 'Product Stock Update');
            $rfc->setData('rfc_url', $rfcUrl);
            $rfc->setData('rfc_username', $rfcUsername);
            $rfc->setData('rfc_password', $rfcPassword);
            $rfc->setData('rfc_datetime', $this->getTodaysDate());
            $rfc->setData('rfc_enable', $rfcEnable);
            $rfc->setData('rfc_status', 'Running');
            $rfc->setData('rfc_run_method', $method);
            $rfc->setData('rfc_ip_address', $ipaddress);
            $rfc->save();
            $rfcid = $rfc->getRfcId();

           // if (count($response) == 0) {
            if($proceed == 0){
                $rfc = $this->_objectManager->create('Hdweb\Rfc\Model\Rfc')->load($rfcid);
                $rfc->setData('rfc_datetime', $this->getTodaysDate());
                $rfc->setData('rfc_status', 'Failed');
                $rfc->setData('rfc_total_record', $totalcount);
                $rfc->setData('rfc_total_sucess', $successcount);
                $rfc->setData('rfc_total_fail', $failedcount);
                $rfc->save();
                //Response Row Data for csv file if response is 0
                $responseRowData[] = array('Requested URL', 'Response Data', 'Status');
                $responseRowData[] = array($rfcUrl, $rfcFunction, 'Failed');
                // If its get an Response
            } else {
				$query = "SELECT * FROM [gcccoastmsdb].[dbo].[WITMAST]";
                $query = "SELECT * FROM [gcccoastmsdb].[dbo].[WITMAST] where ITMODEL >=2021";

				$stmt  = $conn->prepare($query);
				$stmt->execute();
				$response = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if (is_array($response)) {
                    echo "<br>Total Records:- ".count($response).'<br>';
                    $responseRowData   = array();
                    $responseRowData[] = array('Sku', 'Item Description', 'Year', 'Old Qty', 'Qty', 'Vendor Qty', 'Old Price', 'New Price', 'Vendor Price','Offer Price', 'Status', 'Message', 'Price Status', 'Qty Status', 'Offer Status');
                    //Optained Catalog Entity Id
                    $catalogEntitySql = "SELECT entity_type_id FROM " . $eavEntityTypeTable . " WHERE entity_type_code = 'catalog_product' LIMIT 1";
                    $resultCatalog    = $connection->fetchCol($catalogEntitySql);
                    $catalogEntityId  = $resultCatalog[0];

                    //Optained Product From attribute id  ----- PRODUCT YEAR
                    $yearIdSql    = "SELECT attribute_id FROM " . $eavAttributeTable . " WHERE attribute_code = 'dot' AND entity_type_id = " . $catalogEntityId . " LIMIT 1";
                    $yearIdResult = $connection->fetchCol($yearIdSql);
                    $yearId       = $yearIdResult[0];

                    //Optained Product From Options option id
                    $yearIdOptionSql       = "SELECT option_id FROM " . $eavAttributeOptionTable . " WHERE attribute_id = " . $yearId;
                    $yearIdOptionSqlResult = $connection->fetchCol($yearIdOptionSql);
                    //End of Optained Product From attribute id  ----- PRODUCT FROM

                    //Obtained Old Qty
                   // $oldQtyArr      = $this->updateStockQtyBefore($response, $yearIdOptionSqlResult, $eavAttributeOptionValueTable, $connection, $cataloginventoryStockItemTable, $storeScope);
                    $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
                    $_productloader = $objectManager->create('Magento\Catalog\Model\ProductFactory');
                    $oldPrice       = '';

                    foreach ($response as $key => $data) {
                        
                        
                        $dataItcode      = trim($data['ITCODE']);
                        $dataDescription = trim($data['ITDESC']);
                        $dataYear        = trim($data['ITMODEL']);
                        $dataQty         = trim($data['STOCK']);
                        $fittPrice       = trim($data['FITTPRICE']);
                        $vendorPrice     = trim($data['FITTPRICE']);
                        $offerPrice      = trim($data['OFFERPRICE']);



						$newPrice = 0;

                       /* $_product = $this->productCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('gc_item_code', $dataItcode)
                        ->addAttributeToFilter('status', array('in'=>array(1,2)))
                        ->setPageSize(1);
                        $product_col = $_product->load();
                        //print_r($product);
                        echo 'jj00'.$_product->getId();
                        foreach ($product_col as $product){
                            echo 'jj'.$_product->getId();
                        } exit;*/
                        
                        /*$_product = $this->productCollectionFactory->create()
                            ->addAttributeToSelect('*')
                            //->addAttributeToFilter('status', array('in'=>array(1,2)))
                            ->addAttributeToFilter('gc_item_code', $dataItcode);
                        $product = $_product->getData();*/
                        
                        $sql = "SELECT entity_id FROM `catalog_product_entity_varchar` WHERE `attribute_id` = 177 AND `value` = '".$dataItcode."'";         
                        $product = $connection->fetchAll($sql);

                        
                        

                        if ((!empty($product) || $product != null) && $dataYear>=2021) {

                            $productId    = $product[0]['entity_id'];
                            $productPrice = $_productloader->create()->load($productId);
                            $oldPrice     = $productPrice->getPrice();
                            $productofer  = $productPrice->getOffers();
                            $productdot   = $productPrice->getResource()->getAttribute('dot')->getFrontend()->getValue($productPrice);
                            /*print_r($productofer);
                            exit;*/
                            //Update Stock Item Table
                            $isInStock = 0;

                            if ($dataQty > 0) {
                                $isInStock = 1;
                                $qtystatus = "Updated";                               
                                $updateStatussql = "UPDATE ".$catalogProductEntityIntTable. " SET value = '1' WHERE attribute_id = ".$statusAttrId." AND entity_id = ".$productId."";
                            } else {
                                $dataQty   = 0;
                                $qtystatus = "Not Udpated Zero Found";
                                $updateStatussql = "UPDATE ".$catalogProductEntityIntTable. " SET value = '2' WHERE attribute_id = ".$statusAttrId." AND entity_id = ".$productId."";
                            }

                            //$connection->query($updateStatussql);

                            $updateStocksql = "UPDATE " . $cataloginventoryStockItemTable . " SET qty = " . $dataQty . " , is_in_stock = " . $isInStock . " where product_id = " . $productId . "";
                            //echo '<br>'.$updateStocksql.'<br>';
                            $connection->query($updateStocksql);

                            $stockModel = $objectManager->get('Magento\CatalogInventory\Model\Stock\ItemFactory')->create();
                            $stockResource = $objectManager->get('Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
                            $stockResource->load($stockModel, $productId,"product_id");
                            $stockModel->setQty($dataQty);
                            $stockResource->save($stockModel);

							$pricestatus = "Skipped";
                           // if (empty($productofer)) {
								if($offerPrice > 0){
									$updatePriceindexsql = "UPDATE " . $catalogPriceIndexTable . " SET price = " . $offerPrice . " , min_price = " . $offerPrice . ", max_price = " . $offerPrice . ", final_price = " . $offerPrice . " where entity_id = " . $productId . "";
									$updatePricedecimalsql = "UPDATE " . $catalogPriceDecimalTable . " SET value = " . $offerPrice . " where entity_id = " . $productId . "";

									$connection->query($updatePriceindexsql);
									$connection->query($updatePricedecimalsql);
									$fittPrice = $offerPrice;
									$pricestatus = "Updated";
								}else{
									$fittPrice   = "Zero Price Found";
									$pricestatus = "Not Updated"; //Offer- Not Updated
								}
                                
                            /*} else {
                                $fittPrice   = "Offer Price Found";
                                $pricestatus = "Not Updated"; //Offer- Not Updated
                            }*/

                            $offerPricemsg = '';
                            /* if (empty($productofer)) {
                                if ($offerPrice > 0 && ($offerPrice < $fittPrice)) {
                                    $today    = date('Y-m-d', strtotime(' - 1 days'));
                                    $nextYear = date('Y-m-d', strtotime(' + 30 days'));
                                    $productPrice->setSpecialPrice($offerPrice);
                                    $productPrice->setSpecialFromDate($today);
                                    $productPrice->setSpecialToDate($nextYear);
                                    $productResourceModel = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product');
                                    $productResourceModel->saveAttribute($productPrice, 'special_price');
                                    $productResourceModel->saveAttribute($productPrice, 'special_from_date');
                                    $productResourceModel->saveAttribute($productPrice, 'special_to_date');
                                    $productdot    = trim($productdot);
                                    $offerPricemsg = "Offer Price Updated";
                                }
                            } */

                            $productdot = trim($productdot);

                            echo "Stock Update for Product Sku " . $productId . "-" . $dataItcode . "- QTY:- ".$dataQty."- Price:- ".$fittPrice."- Year:-".$dataYear." </br>";
                            $successcount++;
                            $responseRowData[] = array($dataItcode, $dataDescription, $dataYear, $dataQty, $dataQty, $oldPrice, $fittPrice, $vendorPrice,
                                $offerPrice, 'Success', 'Updated', $pricestatus, $qtystatus, $offerPricemsg);

                        } else {
                            echo "Failed Stock Update for Product Sku " . $dataItcode . "-" . $dataYear ."- Year:-".$dataYear." </br>";
                            $failedcount++;
                            $responseRowData[] = array($dataItcode, $dataDescription, $dataYear, 'N/A', 'N/A', $dataQty, 'N/A', $newPrice, $fittPrice,$offerPrice, 'Failed', 'Product Not Found', '', '', '');
                        }
                        $totalcount++;
                        //exit;
                    }

                    $this->_reIndexingAll();

                } else {
                    if ($rfcEnableEmail == 1) {
                        $subject = "RFC connection issue - " . $rfcFunction;
                        $message = "Could not connect to server.</b>";
                        $retval  = mail($to, $subject, $message);
                    }
                    if ($retval == true) {
                        echo "Could not connect to server. Mail sent successfully.";
                    } else {
                        echo "Could not connect to server. Mail could not be sent.";
                    }
                }

                $rfc = $this->_objectManager->create('Hdweb\Rfc\Model\Rfc')->load($rfcid);
                $rfc->setData('rfc_datetime', $this->getTodaysDate());
                $rfc->setData('rfc_status', 'Success');
                $rfc->setData('rfc_total_record', $totalcount);
                $rfc->setData('rfc_total_sucess', $successcount);
                $rfc->setData('rfc_total_fail', $failedcount);
                $rfc->save();

                if ($rfcEnableEmail == 1) {
                    $subject = "RFC run successfully - " . $rfcFunction;
                    $message = "Stock Update RFC executed successfully.";
                    $retval  = mail($to, $subject, $message);

                    if ($retval == true) {
                        echo "Inventory updated. Mail sent successfully!";
                    } else {
                        echo "Inventory updated. Mail could not be sent!";
                    }
                }
            }

            die('Complete');

        } else {
            echo "RFC Settings are disabled.";die();
        }
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getTodaysDate()
    {
        $localeTimezone = $this->_timezoneInterface->getConfigTimezone('store', $this->getStore());
        date_default_timezone_set($localeTimezone);
        return $this->_timezoneInterface->date()->format('Y-m-d H:i:s');
    }

    public function getIpAddress()
    {
        $remoteAddress = $this->_objectManager->create('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        return $remoteAddress->getRemoteAddress();
    }

    public function updateStockQtyBefore($response, $yearIdOptionSqlResult, $eavAttributeOptionValueTable, $connection, $cataloginventoryStockItemTable, $storeScope)
    {
        if (count($response) > 0) {
            $oldQtyArray = array();
            foreach ($response as $data) {
                $dataItcode = trim($data['ITCODE']);
                $_product   = $this->productCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('gc_item_code', $dataItcode);

                $product = $_product->getData();

                if (!empty($product) || $product != null) {
                    $productId = $product[0]['entity_id'];

                    if ($productId != null && $productId != "") {

                        $getOldStocksql          = "SELECT qty FROM " . $cataloginventoryStockItemTable . " WHERE product_id = " . $productId . " LIMIT 1";
                        $oldQtyResult            = $connection->fetchCol($getOldStocksql);
                        $oldQty                  = $oldQtyResult[0];
                        $oldQtyArray[$productId] = $oldQty;
                    }
                }
            }
        }
        return $oldQtyArray;
    }

    public function _reIndexingAll()
    {
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $indexerIds        = array(
            'catalog_product_price',
            'cataloginventory_stock',
        );
        foreach ($indexerIds as $indexerId) {
            $indexer = $this->_indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }
	
	public function getDateDifference($actualDate, $currentDate){
		// Declare and define two dates 
		$date1 = strtotime($actualDate); 
		$date2 = strtotime($currentDate); 

		// Formulate the Difference between two dates 
		$diff = abs($date2 - $date1); 


		// To get the year divide the resultant date into 
		// total seconds in a year (365*60*60*24) 
		$years = floor($diff / (365*60*60*24)); 


		// To get the month, subtract it with years and 
		// divide the resultant date into 
		// total seconds in a month (30*60*60*24) 
		$months = floor(($diff - $years * 365*60*60*24) 
									/ (30*60*60*24)); 


		// To get the day, subtract it with years and 
		// months and divide the resultant date into 
		// total seconds in a days (60*60*24) 
		$days = floor(($diff - $years * 365*60*60*24 - 
					$months*30*60*60*24)/ (60*60*24)); 


		// To get the hour, subtract it with years, 
		// months & seconds and divide the resultant 
		// date into total seconds in a hours (60*60) 
		$hours = floor(($diff - $years * 365*60*60*24 
			- $months*30*60*60*24 - $days*60*60*24) 
										/ (60*60)); 


		// To get the minutes, subtract it with years, 
		// months, seconds and hours and divide the 
		// resultant date into total seconds i.e. 60 
		$minutes = floor(($diff - $years * 365*60*60*24 
				- $months*30*60*60*24 - $days*60*60*24 
								- $hours*60*60)/ 60); 


		// To get the minutes, subtract it with years, 
		// months, seconds, hours and minutes 
		$seconds = floor(($diff - $years * 365*60*60*24 
				- $months*30*60*60*24 - $days*60*60*24 
						- $hours*60*60 - $minutes*60)); 

		/* $difference = sprintf("%d years, %d months, %d days, %d hours, "
			. "%d minutes, %d seconds", $years, $months, 
					$days, $hours, $minutes, $seconds); */
		$difference = sprintf("%d days, %d hours, "
			. "%d minutes", $days, $hours, $minutes);
		$diffArray = array('days' => $days, 'hours' => $hours);		
		return $diffArray;
		/* printf("%d years, %d months, %d days, %d hours, "
			. "%d minutes, %d seconds", $years, $months, 
					$days, $hours, $minutes, $seconds); */
		
	}
}
