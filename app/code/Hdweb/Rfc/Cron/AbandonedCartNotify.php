<?php
namespace Hdweb\Rfc\Cron;

use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollectionFactory;
use Magento\Quote\Model\Quote;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Api\CartRepositoryInterface;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
class AbandonedCartNotify
{
    /**
     * @var QuoteCollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var QuoteItemCollectionFactory
     */
    protected $QuoteItemCollectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $_orderCollectionFactory;
    protected $abandonedCartNotifyLogger;
    protected $orderFactory;
    protected $timezone;

    protected $transportBuilder;
    protected $inlineTranslation;
    protected $quoteRepository;

    protected $productRepository;
    protected $storeManager;
    /**
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param CustomerFactory $customerFactory
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        QuoteCollectionFactory $quoteCollectionFactory,
        QuoteItemCollectionFactory $QuoteItemCollectionFactory,
        CustomerFactory $customerFactory,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
         TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
         CartRepositoryInterface $quoteRepository,
         ProductRepositoryInterface $productRepository,
         StoreManagerInterface $storeManager
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->QuoteItemCollectionFactory = $QuoteItemCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->timezone = $timezone;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/abandoned_cart_notify_cron.log');
        $this->abandonedCartNotifyLogger = new \Zend_Log();
        $this->abandonedCartNotifyLogger->addWriter($writer);
    }

    /**
     * Execute abandoned cart cron job
     *
     * @return void
     */
    public function execute()
    {
        //WHEN GO LIVE NEED TO COMMENT ALL THIS LINE WHERE I HAVE WRITTEN "added for test"
        $this->abandonedCartNotifyLogger->info('------------------------- Abandoned Cart Notify Cron Starts  -------------------------');
        $startTime = microtime(true);
        
        $abandonedCarts = $this->getAbandonedCartCollection();
        $count = 0; //added for test

        foreach ($abandonedCarts as $cart) {
        
            $count++; //added for test

            try {
                $isMailSent = $this->sendEmail($cart);

                $this->abandonedCartNotifyLogger->info("Notify called for Quote {$cart->getId()}: Response and count saved in quote");
                try {
                    if($isMailSent)
                    {
                        $loadedQuote = $this->quoteRepository->get($cart->getId());
                        $loadedQuote->setData('abd_cron_mail_date', date('Y-m-d H:i:s'));
                        $loadedQuote->setData('abd_cron_mail_status', 1);

                        $this->quoteRepository->save($loadedQuote);
                        $this->abandonedCartNotifyLogger->info("Cron status for Quote {$cart->getId()}: Updated successfully.");
                    }
                } catch (\Exception $e) {
                    // Log the error for this quote
                    $this->abandonedCartNotifyLogger->info("Quote not found for: {$cart->getId()}");
                }
               if ($count >= 256) { break; } //added for test

            } catch (\Exception $e) {
                $this->abandonedCartNotifyLogger->info("Failed to process cart {$cart->getId()}: " . $e->getMessage());
                if ($count >= 2) { break; } //added for test
                continue;
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        $this->abandonedCartNotifyLogger->info('Abandoned Cart Notify completed in ' . $duration . ' seconds');
        $this->abandonedCartNotifyLogger->info('------------------------- Abandoned Cart Notify Cron Ends ### -------------------------');
     }

    public function getOrdersByQuoteId($quoteId)
    {
        $orderCollection = $this->_orderCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId);
        $orders = [];
        foreach ($orderCollection as $order) {
            $orders[] = $order; 
           // echo 'Order ID: ' . $order->getId() . ' | Increment ID: ' . $order->getIncrementId() . '<br>';
           $this->abandonedCartNotifyLogger->info('Order ID: ' . $order->getId() . ' | Increment ID: ' . $order->getIncrementId() . '<br>');
             // echo "<strong>Order ID:</strong> " . $order->getId() . "<br>";
            // echo "<strong>Increment ID:</strong> " . $order->getIncrementId() . "<br>";
            $this->abandonedCartNotifyLogger->info('Email: ' . $order->getcustomer_email() . '<br>');
        
            // Shipping Address
            // $shippingAddress = $order->getShippingAddress();
            // if ($shippingAddress) {
            //     $this->abandonedCartNotifyLogger->info('<strong>Shipping Address:</strong><br>');
            // $this->abandonedCartNotifyLogger->info('Name: ' .$shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname() . '<br>');
            //     $street = $shippingAddress->getStreet();
            //     $this->abandonedCartNotifyLogger->info($street[0]);
            //     $this->abandonedCartNotifyLogger->info('City: ' .$shippingAddress->getCity() . ', ');
            //     $this->abandonedCartNotifyLogger->info('PostCode: ' .$shippingAddress->getPostcode() . ', ');
            //     $this->abandonedCartNotifyLogger->info('Country: ' .$shippingAddress->getCountryId()  . ', ');
            //     $this->abandonedCartNotifyLogger->info('Phone: ' . $shippingAddress->getTelephone() . ', ');
            // }

            // Order Items
            foreach ($order->getAllVisibleItems() as $item) {
                $this->abandonedCartNotifyLogger->info('SKU: ' . $item->getSku()  . ', ');
                $this->abandonedCartNotifyLogger->info('Name: ' .  $item->getName()  . ', ');
                $this->abandonedCartNotifyLogger->info('Qty Ordered: ' . $item->getQtyOrdered()  . ', ');
            }
        }

         return $orders; // ✅ return all order objects
    }

    /**
     * Get customer phone number from cart
     *
     * @param Quote $cart
     * @return string|null
     */
    protected function getCustomerPhone($cart)
    {
        $billingAddress = $cart->getBillingAddress();
        if ($billingAddress && $billingAddress->getTelephone()) {
            return $billingAddress->getTelephone();
        }
        
        $shippingAddress = $cart->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getTelephone()) {
            return $shippingAddress->getTelephone();
        }
        
        return null;
    }

    /**
     * Get abandoned cart collection
     *
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */

    protected function getAbandonedCartCollection()
    {
        $abandonedQuoteIds = $this->getValidAbandonedCartQuoteIds();
        $canceledQuoteIds = $this->getCanceledOrderQuoteIds();
        
        $combinedQuoteIds = array_unique(array_merge($abandonedQuoteIds, $canceledQuoteIds));

        $abandonedCartCollection = $this->quoteCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('abd_cron_mail_status', 0)
            ->addFieldToFilter('entity_id', ['in' => $combinedQuoteIds]);

        $abandonedCartCollection->getSelect()->where(
            'main_table.entity_id NOT IN (
                SELECT quote_id 
                FROM sales_order 
                WHERE status = "processing"
            )'
        );

        return $abandonedCartCollection;
    }

    private function getValidAbandonedCartQuoteIds()
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Dubai'));
        $tenMinutesAgo = (clone $now)->modify('-15 minutes')->format('Y-m-d H:i:s');

        $collection = $this->quoteCollectionFactory->create()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('items_count', ['gt' => 0])
            ->addFieldToFilter('reserved_order_id', ['null' => true])
            ->addFieldToFilter('created_at', ['lteq' => $tenMinutesAgo]);
        
        return array_filter($collection->getColumnValues('entity_id'));
    }

    /**
     * Get quote IDs associated with canceled orders
     *
     * @return array
     */
    private function getCanceledOrderQuoteIds()
    {
        $orderCollection = $this->_orderCollectionFactory->create()
            ->addFieldToSelect('quote_id');

        $orderCollection->getSelect()
            ->join(
                ['payment' => $orderCollection->getTable('sales_order_payment')],
                'main_table.entity_id = payment.parent_id',
                ['method']
            )
            ->where("
                main_table.status IN ('canceled','pending_payment') 
                OR (main_table.status = 'pending' AND payment.method != ?)
            ", 'cashondelivery');
        
        $this->abandonedCartNotifyLogger->info('Quote IDs for canceled orders and pending orders: ' . print_r($orderCollection->getColumnValues('quote_id'), true));

        return array_filter($orderCollection->getColumnValues('quote_id'));
    }

    protected function sendEmail($cart)
    {
        try {
             $email = 'testmail.hdit@gmail.com';//$this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
             $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
            $toEmail = 'amit.synex@gmail.com'; //'alice@klever.ae';
           // $ccEmail = 'rayan@alnouftire.com'; //'nikhil@klever.ae';
            //$bccEmail = 'patelalicen@gmail.com';
            $this->abandonedCartNotifyLogger->info('gmail='.$email);
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $templateVars = [
                                    'name' => $cart['customer_firstname'] ?? '',
                                    'email' => $cart['customer_email'] ?? '',
                                    'number' => $cart['customer_phone'] ?? '',
                                ];

            $quoteitemsCollection = $this->QuoteItemCollectionFactory->create();
            $quoteitemsCollection->addFieldToFilter('quote_id', $cart->getId());
       
            $productHtml = '';
            
            foreach ($quoteitemsCollection as $item) {
    
                       try {
                                $product = $this->productRepository->get($item->getSku());
                                $imagePath = 'catalog/product' . $product->getData('thumbnail');
                                $thumbnailUrl = $mediaUrl . $imagePath;
                            } catch (\Exception $e) {
                                $thumbnailUrl = ''; // Fallback
                               
                            }

                            $productHtml .= "<p><strong>Product: {$item->getName()}</strong><br><strong>SKU: {$item->getSku()}</strong><br><strong>Qty: {$item->getQty()}</strong> </p>";
    
            }
            $this->abandonedCartNotifyLogger->info('Outer Products_html start');
            $templateVars['products_html'] = $productHtml;
            $this->abandonedCartNotifyLogger->info('Product DATA  for Quote '.$cart->getId().'::'. print_r($templateVars['products_html'], true));
            $this->abandonedCartNotifyLogger->info('Outer Products_html END');

            $orders = $this->getOrdersByQuoteId($cart->getId());
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
          //  $this->abandonedCartNotifyLogger->info('ORDERS DATA:: ' . print_r($cart->getData(), true));
                                $this->abandonedCartNotifyLogger->info('testing');

            foreach ($orders as $order) {
                   // $this->abandonedCartNotifyLogger->info('ORDER DATA::'. print_r(get_class_methods($order), true));
                    $templateVars = [
                        'name' => $cart['customer_firstname'] ?? $order->getCustomerName() ?? '',
                        'email' => $cart['customer_email'] ?? $order->getCustomerEmail() ?? '',
                        'number' => $cart['customer_phone'] ?? '',
                        'order_id' => $order->getIncrementId(),
                        //'customer_email' => $order->getCustomerEmail() ?? '',
                        //'customer_name'  => $order->getCustomerName() ?? '', // or custom logic
                    ];

                    $productHtml = '';
                    foreach ($order->getAllVisibleItems() as $item) {
                       try {
                                $product = $this->productRepository->get($item->getSku());
                                $imagePath = 'catalog/product' . $product->getData('thumbnail');
                                $thumbnailUrl = $mediaUrl . $imagePath;
                            } catch (\Exception $e) {
                                $thumbnailUrl = ''; // Fallback
                               
                            }

                            $productHtml .= "<p><strong>Product: {$item->getName()}</strong><br><strong>SKU: {$item->getSku()}</strong><br><strong>Qty: {$item->getQtyOrdered()}</strong> </p>";
                    }

                    $templateVars['products_html'] = $productHtml;

                    $this->abandonedCartNotifyLogger->info('Inner Products_html start');
                    $this->abandonedCartNotifyLogger->info('Product DATA::'. print_r($templateVars['products_html'], true));
                    $this->abandonedCartNotifyLogger->info('Inner Products_html END');

                    //$shippingAddress = $order->getShippingAddress();
                    $shippingHtml = '';

                    // if ($shippingAddress) {
                    //     $streetLines = $shippingAddress->getStreet(); // returns array of street lines

                    //     $shippingHtml .= "<p>";
                    //     $shippingHtml .= 'Name: ' .$shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname() . "<br>";
                        
                    //     foreach ($streetLines as $line) {
                    //         $shippingHtml .= 'Street: ' .$line . "<br>";
                    //     }

                    //     $shippingHtml .= 'City: ' .$shippingAddress->getCity() . ', ';
                    //     $shippingHtml .= 'Region: ' .$shippingAddress->getRegion() . ', ';
                    //     $shippingHtml .= 'PostCode: ' .$shippingAddress->getPostcode() . '<br>';
                    //     $shippingHtml .= 'Country: ' .$shippingAddress->getCountryId() . '<br>';
                    //     $shippingHtml .= 'Telephone: ' . $shippingAddress->getTelephone();
                    //     $shippingHtml .= "</p>";
                    // }

                    $templateVars['shippingHtml'] = $shippingHtml;
                    $billingAddress = $order->getBillingAddress();
                    $billingHtml = '';

                    if ($billingAddress) {
                        // $streetLines = $billingAddress->getStreet(); // returns array of street lines

                        // $billingHtml .= "<p>";
                        // $billingHtml .= 'Name: ' .$billingAddress->getFirstname() . ' ' . $billingAddress->getLastname() . "<br>";
                        
                        // foreach ($streetLines as $line) {
                        //     $billingHtml .= 'Street: ' .$line . "<br>";
                        // }
                        // $billingHtml .= 'City: ' .$billingAddress->getCity() . ', ';
                        // $billingHtml .= 'Region: ' .$billingAddress->getRegion() . ', ';
                        // $billingHtml .= 'PostCode: ' .$billingAddress->getPostcode() . '<br>';
                        // $billingHtml .= 'Country: ' .$billingAddress->getCountryId() . '<br>';
                        // $billingHtml .= 'Telephone: ' . $billingAddress->getTelephone();
                        // $billingHtml .= "</p>";

                        if ($billingAddress->getTelephone()) {
                            $templateVars['number'] = $billingAddress->getTelephone();
                        }
                    }

                $templateVars['billingHtml'] = $billingHtml;

                $this->abandonedCartNotifyLogger->info('TEMPLATE VARS:::::::'. print_r($templateVars, true));
            }
            
            $this->abandonedCartNotifyLogger->info('Template Variable::'. print_r($templateVars, true));      

            if (empty(array_filter($templateVars))) {
                $loadedQuote = $this->quoteRepository->get($cart->getId());
                $loadedQuote->setData('abd_cron_mail_date', date('Y-m-d H:i:s'));
                $loadedQuote->setData('abd_cron_mail_status', 2);

                $this->quoteRepository->save($loadedQuote);
                $this->abandonedCartNotifyLogger->info("Cron status for Quote {$cart->getId()}: Updated successfully with abd_cron_mail_status = 2.");
                $this->abandonedCartNotifyLogger->info('Mail did not Sent because there is no TEMPLATE VARS');
                return false;
            }elseif(empty($templateVars['email'])){
                if(empty($templateVars['number'])){
                    try{
                        $loadedQuote = $this->quoteRepository->get($cart->getId());
                        $loadedQuote->setData('abd_cron_mail_date', date('Y-m-d H:i:s'));
                        $loadedQuote->setData('abd_cron_mail_status', 2);

                        $this->quoteRepository->save($loadedQuote);
                    }catch(\Exception $e){
                        $this->abandonedCartNotifyLogger->info($e->getMessage());
                    }
                    

                    $this->abandonedCartNotifyLogger->info("Cron status for Quote {$cart->getId()}: Updated successfully with abd_cron_mail_status = 2.");
                    $this->abandonedCartNotifyLogger->info('Mail did not Sent because there is no email address');
                    return false;
                }else{

                    $templateOptions = [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ];

                $this->inlineTranslation->suspend();
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier(18) // Or use $templateIdentifier if it's set correctly
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom(['email' => $email, 'name' => $name])
                    ->addTo($toEmail)
                    //->addCc($ccEmail)
                    //->addBcc($bccEmail)
                    ->getTransport();

                $transport->sendMessage();
                $this->inlineTranslation->resume();

                $this->abandonedCartNotifyLogger->info('Mail Sent with TEMPLATE VARS::'. print_r($templateVars, true));

                return true;

            }

            }else{
                $templateOptions = [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ];

                $this->inlineTranslation->suspend();
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier(18) // Or use $templateIdentifier if it's set correctly
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom(['email' => $email, 'name' => $name])
                    ->addTo($toEmail)
                    ->addTo($toEmail)
                    //->addCc($ccEmail)
                    //->addBcc($bccEmail)
                    ->getTransport();

                $transport->sendMessage();
                $this->inlineTranslation->resume();

                $this->abandonedCartNotifyLogger->info('Mail Sent with TEMPLATE VARS::'. print_r($templateVars, true));

                return true;
            }
        } catch (\Exception $e) {
            file_put_contents(BP . '/var/log/abandoned_cart_notify_cron.log', $e->getMessage(), FILE_APPEND);
            return false;
        }

        return false;
    }
}