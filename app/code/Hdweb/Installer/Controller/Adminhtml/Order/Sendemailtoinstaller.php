<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class Sendemailtoinstaller extends \Magento\Backend\App\Action
{
    const NOTIFY_INSTALLER_TEMPLATE  = 'installer/general/admin_installer_email_template';
 
    protected $_order;
    protected $scopeConfig;
    protected $pickupstores;
    protected $transportBuilder;
    protected $stateInterface;
    protected $storeManagerInterface;
    protected $country;
    protected $addressRenderer;
    protected $paymentHelper;
    protected $identityContainer;
    protected $_filesystem;
    protected $fileFactory;
    protected $file;
    protected $dir;
    protected $orderStatusRepository;
    protected $authSession;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomteck\StoreLocator\Model\Stores $pickupstores,
        \Hdweb\Core\Model\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $stateInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Directory\Model\Country $country,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderIdentity $identityContainer,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        \Magento\Backend\Model\Auth\Session $authSession

    ) {
        parent::__construct($context);
        $this->_order          = $order;
        $this->scopeConfig     = $scopeConfig;
        $this->pickupstores    = $pickupstores;
        $this->transportBuilder          = $transportBuilder;
        $this->stateInterface     = $stateInterface;
        $this->storeManagerInterface    = $storeManagerInterface;
        $this->country    = $country;
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->identityContainer = $identityContainer;
         $this->_filesystem     = $filesystem;
        $this->fileFactory     = $fileFactory;
        $this->file            = $file;
        $this->dir             = $dir;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->authSession = $authSession;
    }
    public function execute()
    {

        $order_id = $this->getRequest()->getParam('id');

        $admin_installer_date = $this->getRequest()->getParam('installer_date');

        $admin_installer_comment = $this->getRequest()->getParam('installer_comment');

        $order = $this->_order->load($order_id);

        // Save workorder fields to DB
        $order->setWorkorderDatetime($admin_installer_date);
        $order->setWorkorderComment($admin_installer_comment);

        $installer_id=$order->getPickupStore();

        if(isset($installer_id) && !empty($installer_id) ) {

         $pickupstoresData=$this->pickupstores->load($installer_id);
         $installer_email=$pickupstoresData->getEmail();
        
            if (!empty($installer_email) && strpos($installer_email, '@') !== false) {
                $_transportBuilder = $this->transportBuilder;
                $inlineTranslation = $this->stateInterface;
                $storeManager      = $this->storeManagerInterface;
                $storeManager->setCurrentStore($order->getStore()->getId());
                $country = $this->country->load($pickupstoresData->getCountry())->getName();
                
                $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());

                $payment            = $order->getPayment();
                $method             = $payment->getMethodInstance();
                $paymentmethodTitle = $method->getTitle();
                $order->setIsnotifyinstaller(1);
                if ($order->getVinNumber()) {
                    $vehicleVinNo = $order->getVinNumber();
                } else {
                    $vehicleVinNo = '';
                }
				$baseUrl = $storeManager->getStore()->getBaseUrl();
				$installerTokenkey = md5($order->getIncrementId());
				$installationLink = $baseUrl.'installer/ajax/installationcomplete/order_id/'.$installerTokenkey;
                $templateVars = [
                'order_id' => $order_id,
                'order' => $order,
                'orderitem' => $order->getAllItems(),
                'billing' => $order->getBillingAddress(),
                'payment_html' => $paymentmethodTitle,//$this->getPaymentHtml($order),
                'store' => $order->getStore(),
                //'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                //'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'formattedShippingAddress' => $order->getCustomerName(),
                'formattedBillingAddress' => $order->getCustomerName(),
                'created_at_formatted' => $order->getCreatedAtFormatted(2),
                'admin_installer_date' => $admin_installer_date,
                'admin_installer_comment' => $admin_installer_comment,
                'installer_name'          => $pickupstoresData->getName(),
                'installer_street'        => $pickupstoresData->getAddress(),
                'installer_city'          => $pickupstoresData->getCity(),
                'installer_region'        => $pickupstoresData->getRegion(),
                'installer_country'       => $country,
                'installer_managername'   => '',//$installer_detail['storemanager_name'],
                'installer_email'         => $pickupstoresData->getEmail(),
                'installer_phone'         => 'T: '.$pickupstoresData->getPhone(),  
                'installer_location_map'  => $pickupstoresData->getExternalLink(),   
                'vehicle_plate'           => $order->getPlate(),     
                'vehicle_vinno'           => $vehicleVinNo,     
                'vehicle_make'            => $order->getMake(),     
                'vehicle_model'           => $order->getModel(),     
                'vehicle_year'            => $order->getYear(),
				'installation_link' 	  => $installationLink,	
                'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'is_not_virtual' => $order->getIsNotVirtual(),
                'email_customer_note' => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];
                $pdfdownload = $this->saveworkorderpdf($templateVars);
                $templateVars['pdfdownload'] = $pdfdownload['pdfdownload'];

                $email                       = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
                $name                        = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
                $copy_to             = $this->scopeConfig->getValue('sales_email/order/copy_to', ScopeInterface::SCOPE_STORE);
                $from                = array('email' => $email, 'name' => $name);
                $receiveremail       = $pickupstoresData->getEmail();

                $storeEmailCopy = $pickupstoresData->getEmailCopy();
                $receiverEmailsArray = explode(',', $storeEmailCopy);
                array_push($receiverEmailsArray,$receiveremail);
                

                $inlineTranslation->suspend();


                $notifyInstallerTemplate=$this->scopeConfig->getValue(self::NOTIFY_INSTALLER_TEMPLATE, ScopeInterface::SCOPE_STORE);
                
                $transport = $_transportBuilder->setTemplateIdentifier($notifyInstallerTemplate)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($receiverEmailsArray)
                        
                        //->addCc($copy_to)
                    ->addAttachment($pdfdownload['pdfData'], $pdfdownload['filename'], 'application/pdf')
                    ->getTransport();

                // Debug: check PDF data length before sending
                if (empty($pdfdownload['pdfData']) || strlen($pdfdownload['pdfData']) < 10) {
                    $this->messageManager->addError(__('PDF attachment data is empty or invalid.'));
                } else {
                    $transport->sendMessage();
                }
                $inlineTranslation->resume();


                $this->messageManager->addSuccess(__('Email has been sent.'));
                /*$order->addStatusHistoryComment('Notify - Installer - ' . $admin_installer_date . ' - ' . $admin_installer_comment);*/
                $adminUser = $this->authSession->getUser();
                $comment = $order->addStatusHistoryComment(
                    'Notify - Installer - ' . $admin_installer_date . ' - ' . $admin_installer_comment . ' - BY ' . $adminUser->getFirstname(). ' '.$adminUser->getLastname()
                );
                try {
                    $orderHistory = $this->orderStatusRepository->save($comment);
                    
                    /* Start WhatsApp Notification */
                    if($pickupstoresData->getWhatsappNumber()){
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $postData = array('date_time' => $admin_installer_date, 'comment' => $admin_installer_comment, 'installer_name' => $pickupstoresData->getName(), 'phone' => $pickupstoresData->getWhatsappNumber());
                        $responsFinal = $objectManager->create('Hdweb\Rfc\Helper\Data')->sendWhatsAppNotification($order, $templateId = 56764, $notifyInstaller = true, $notifyCustomer = null, $orderUpdateComment = null, $po = null, $postData);
                    }
                    /* End WhatsApp Notification */
                } catch (\Exception $exception) {
                    $this->logger->critical($exception->getMessage());
                }


				$order->setIsNotifyInstaller($installerTokenkey);
                $order->save();
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
            } else {
                $this->messageManager->addError(__('Please add installer email address.'));
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
            }

       }else{
               $this->messageManager->addError(__('Installer not found for this order'));
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
         
       }     

    }
    
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    protected function getPaymentHtml($order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }

    public function saveworkorderpdf($templateVars)
    {
        error_reporting(E_ALL ^ E_DEPRECATED); // temp fix
        $pdf          = new \Zend_Pdf();
        $pdf->pages[] = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4);
        //$pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0]; // this will get reference to the first page.
        //   $page1 = $pdf->pages[1]; // this will get reference to the first page.
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $width        = $page->getWidth();
        $hight        = $page->getHeight();
        $x            = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y      = 850 - 170; //print table row from page top – 100px
        $style->setFont($font, 10);
        $page->setStyle($style);

        $imagePath = 'logo.png';

        $image = "";
        if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath));
        }
        $pdfSalesEmail = '';
        $salesEmail    = $this->scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE);
        if ($salesEmail != '') {
            $pdfSalesEmail = $salesEmail;
        }

        $pdfStoreName = '';
        $storeName    = $this->scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE);
        if ($storeName != '') {
            $pdfStoreName = $storeName;
        }

        $pdfStorePhone = '';
        $storePhone    = $this->scopeConfig->getValue('general/store_information/phone', ScopeInterface::SCOPE_STORE);
        if ($storePhone != '') {
            $pdfStorePhone = $storePhone;
        }

        $y1 = 800;
        $y2 = 830;
        $x1 = 20;
        $x2 = 150;

        $page->drawImage($image, $x1, $y1, $x2, $y2);
        //$font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES_BOLD);
        //        $page->drawRectangle(30, $this->y - 20, $page->getWidth() - 30, $this->y + 110, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText('New Work Order', $x, $this->y + 90, 'UTF-8');
        $order = $templateVars['order'];

        $orderid = $order->getIncrementId();
        // $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0)); //black
        // $page->setLineWidth(0.5);
        // $page->drawLine($x, $this->y + 70, 200, 100);
        $y3   = 740;
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Order #" . $orderid), $x, $y3, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Dear Installer,"), $x, $y3 - 20, 'UTF-8');
        $page->drawText(__("Thank you for accepting " . $pdfStoreName . " Work Order #" . $orderid), $x, $y3 - 35, 'UTF-8');
        $page->drawText(__("Please find the details below :"), $x, $y3 - 50, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Customer Details"), $x, $y3 - 80, 'UTF-8');

        $customername = $order->getCustomerName();

        $font         = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__($customername), $x, $y3 - 95, 'UTF-8');

        $font         = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->drawText(__("Installer Information"), $x + 300, $y3 - 125, 'UTF-8');
        
      //  $installer_detail = unserialize($order->getInstallerDetail());
        //$installer_detail['name']="";
         $street           = $templateVars['installer_street'];
         $street           = wordwrap($street, 50, "&&");
         $street           = explode('&&', $street);
        //$street           = "75 Al Safa Street, Al Safa 75 Al Safa Street, Al Safa";
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText($templateVars['installer_name'], $x + 300, $y3 - 140, 'UTF-8');
        $streetbreak = 155;
        foreach ($street as $key => $value) {
            $page->drawText($value, $x + 300, $y3 - $streetbreak, 'UTF-8');
            $streetbreak += 15;
        }
        $streetbreak = $streetbreak;
        //  $page->drawTextBlock($street, 10, 600, 500, 500, Zend_Pdf_Page::ALIGN_LEFT);
        $page->drawText($templateVars['installer_city'], $x + 300, $y3 - $streetbreak, 'UTF-8');
        $streetbreak = $streetbreak + 15;
        $page->drawText($templateVars['installer_country'], $x + 300, $y3 - $streetbreak, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Vehicle Information"), $x, $y3 - 125, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Plate No.: " . $order->getPlate()), $x, $y3 - 140, 'UTF-8');
      //  $page->drawText(__("VIN No.: " . $templateVars['v_vin']), $x, $y3 - 155, 'UTF-8');  
        $page->drawText(__("Make: " . $order->getMake()), $x, $y3 - 155, 'UTF-8');
        $page->drawText(__("Model: " . $order->getModel()), $x, $y3 - 170, 'UTF-8');
        $page->drawText(__("Year: " . $order->getYear()), $x, $y3 - 185, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Delivery Date/Time :"), $x, $y3 - 215, 'UTF-8');

        $DeliveryDate=date('y-m-d',strtotime($order->getDeliveryDate()));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        //$page->drawText($DeliveryDate.' '.$order->getDeliveryComment(), $x + 100, $y3 - 215, 'UTF-8');
        $page->drawText($templateVars['admin_installer_date'], $x + 100, $y3 - 215, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Order Updates"), $x, $y3 - 235, 'UTF-8');
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText($templateVars['admin_installer_comment'], $x, $y3 - 260, 'UTF-8');

        $comments = str_split("", 120);// jig - $templateVars['admin_installer_comment']
        if (isset($comments[0])) {
            $page->drawText($comments[0], $x, $y3 - 250, 'UTF-8');
        }
        if (isset($comments[1])) {
            $page->drawText($comments[1], $x, $y3 - 262, 'UTF-8');
        }
        if (isset($comments[2])) {
            $page->drawText($comments[2], $x, $y3 - 274, 'UTF-8');
        }
        if (isset($comments[3])) {
            $page->drawText($comments[3], $x, $y3 - 286, 'UTF-8');
        }

        // $page->setFillColor(new \Zend_Pdf_Color_Rgb(216, 0, 0));
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 255));
        //$page->setFillColor(new \Zend_Pdf_Color_Html('#4BCF2E'));
        $page->drawRectangle($x, $y3 - 300, $x + 520, $y3 - 320);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(216, 0, 0));
        //$page->setFillColor(new \Zend_Pdf_Color_Html('#4BCF2E'));
        $style->setFont($font, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(255, 255, 255));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('ITEM'), $x + 10, $y3 - 313, 'UTF-8');
        $page->drawText(__('QTY'), $x + 470, $y3 - 313, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);

        $topy = 340;

        $ordersobj  = $templateVars['order'];
        $orderItems = $ordersobj->getAllItems();
        //$orderItems = array(array('name' => '1111', 'qty' => 3), array('name' => '1111', 'qty' => 3), array('name' => '1111', 'qty' => 3));
        foreach ($orderItems as $key => $item) {
            $name = $item->getName();
            $qty  = (int) $item->getQtyOrdered();
            // $name = $item['name'];
            // $qty  = $item['qty'];

            $page->drawText($name, $x + 10, $y3 - $topy, 'UTF-8');
            $page->drawText($qty, $x + 470, $y3 - $topy, 'UTF-8');
            $topy += 20;
        }
        $yy3  = $topy + 30;
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Please note the following,"), $x, $y3 - $yy3, 'UTF-8');
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $yy3 = $yy3 + 20;
        $page->drawText(__("1. The tyres supplied to you under this work order must be fitted ONLY to the Vehicle bearing the license plate number and "), $x, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 15;
        $page->drawText(__("description specified here. Please contact us if the customer insists on fitting these to another vehicle. "), $x, $y3 - $yy3, 'UTF-8');
        //$yy3 = $yy3 + 15;
        //$page->drawText(__(""), $x, $y3 - $yy3, 'UTF-8');

        $yy3 = $yy3 + 20;
        $page->drawText(__("2. Please refuse to accept delivery if the tyres being delivered by the courier are not the exact same specifications and DOT as listed here"), $x, $y3 - $yy3, 'UTF-8');
        //$yy3 = $yy3 + 15;
        //$page->drawText(__("."), $x, $y3 - $yy3, 'UTF-8');

        $yy3  = $yy3 + 20;
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("3. The customer has paid for new tyres, delivery, installation, balancing and disposal of old tyres.You will have to charges the customer"), $x, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 15;
        $page->drawText(__(" directly for Alignment or any other service you provide."), $x, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 20;
        $page->drawText(__("4. The work order will not be considered complete unless the customer sign this work order and you email back a scanned copy to"), $x, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 15;
        $page->drawText(__(" your " . $pdfStoreName . " contact."), $x, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 30;
        $page->drawText(__("Please feel free to call us at " . $pdfStorePhone . " or Write to us at " . $pdfSalesEmail), $x, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 30;
        $page->drawText(__("Job Complete (Y/N):_________"), $x, $y3 - $yy3, 'UTF-8');
        $page->drawText(__("Remarks (if any):_____________"), $x + 300, $y3 - $yy3, 'UTF-8');
        $yy3 = $yy3 + 30;
        $page->drawText(__("Date & Time:_______________"), $x, $y3 - $yy3, 'UTF-8');
        $page->drawText(__("Customer Signature:__________"), $x + 300, $y3 - $yy3, 'UTF-8');

       
        $pdfData = $pdf->render();

        $workorderdir = $this->dir->getPath('media') . '/workorder';
        if (!file_exists($workorderdir)) {
            mkdir($workorderdir, 0775, true);
        }

        $fileName = $orderid . '_' . time() . '.pdf';
        $workorderpath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'workorder/' . $fileName;
        file_put_contents($workorderpath, $pdfData);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return [
            'filename' => $fileName,
            'pdfData' => $pdfData,
            'pdfdownload' => $mediaUrl . 'workorder/' . $fileName
        ];
    }
    
}
