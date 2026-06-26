<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;


class Sendemailtocustomer extends \Magento\Backend\App\Action
{
   
  
    const NOTIFY_CUSTOMER_TEMPLATE  = 'installer/general/admin_notify_customer_email_template';

 
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
    protected $authSession;
    protected $_filesystem;
    protected $fileFactory;
    protected $file;
    protected $dir;

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
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\DirectoryList $dir

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
        $this->authSession = $authSession;
        $this->_filesystem     = $filesystem;
        $this->fileFactory     = $fileFactory;
        $this->file            = $file;
        $this->dir             = $dir;
    }
    public function execute()
    {

        

         $order_id = $this->getRequest()->getParam('order_id');

        $admin_installer_date = $this->getRequest()->getParam('installer_date');

        $admin_installer_comment = $this->getRequest()->getParam('installer_comment');

        if (empty(trim($admin_installer_comment))) {
            $admin_installer_comment = 'Please present this to the Installer Partner to verify and proceed with the fitment.';
        }

        $order = $this->_order->load($order_id);

        // Save appointment fields to DB
        $order->setAppointmentDatetime($admin_installer_date);
        $order->setAppointmentComment($admin_installer_comment);

        $installer_id=$order->getPickupStore();

        if(isset($installer_id) && !empty($installer_id) ) {

         $pickupstoresData=$this->pickupstores->load($installer_id);
         $installer_email=$order->getCustomerEmail();
        
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
                if ($order->getVinNumber()) {
                    $vehicleVinNo = $order->getVinNumber();
                } else {
                    $vehicleVinNo = '';
                }
               
                $templateVars = [
                'order' => $order,
                'order_id' => $order->getId(),
                'orderitem' => $order->getAllItems(),
                'billing' => $order->getBillingAddress(),
                'payment_html' => $paymentmethodTitle,//$this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
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
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];

                $pdfdownload = $this->saveAppointmentPdf($templateVars);
                $templateVars['pdfdownload'] = $pdfdownload['pdfdownload'];

                $email                       = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
                $name                        = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
                $copy_to             = $this->scopeConfig->getValue('sales_email/order/copy_to', ScopeInterface::SCOPE_STORE);
                $from                = array('email' => $email, 'name' => $name);
                $inlineTranslation->suspend();
                $receiveremail       = explode(',', $installer_email);

                $notifyInstallerTemplate=$this->scopeConfig->getValue(self::NOTIFY_CUSTOMER_TEMPLATE, ScopeInterface::SCOPE_STORE);

                $transport = $_transportBuilder->setTemplateIdentifier($notifyInstallerTemplate)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($installer_email)
                        ->addCc($copy_to)
                    ->addAttachment($pdfdownload['pdfData'], $pdfdownload['filename'], 'application/pdf')
                    ->getTransport();

                if (empty($pdfdownload['pdfData']) || strlen($pdfdownload['pdfData']) < 10) {
                    $this->messageManager->addError(__('PDF attachment data is empty or invalid.'));
                } else {
                    $transport->sendMessage();
                }
                $inlineTranslation->resume();

                $this->messageManager->addSuccess(__('Email has been sent.'));
                $adminUser = $this->authSession->getUser();
                $order->addStatusHistoryComment('Notify - Customer - ' . $admin_installer_date . ' - ' . $admin_installer_comment . ' - BY ' . $adminUser->getFirstname(). ' '.$adminUser->getLastname());
                $order->save();

                /* Start WhatsApp Notification */
                /*$billingAddress  = $order->getBillingAddress();
                if($billingAddress->getTelephone()){
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $postData = array('date_time' => $admin_installer_date, 'comment' => $admin_installer_comment);
                    $responsFinal = $objectManager->create('Hdweb\Rfc\Helper\Data')->sendWhatsAppNotification($order, $templateId = 56839, $notifyInstaller = null, $notifyCustomer = true, $orderUpdateComment = null, $po = null, $postData);
                }*/
                /* End WhatsApp Notification */
                
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
            } else {
                $this->messageManager->addError(__('Please add customer email address.'));
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
            }

       }else{
               $this->messageManager->addError(__('Customer not found for this order'));
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

    public function saveAppointmentPdf($templateVars)
    {
        error_reporting(E_ALL ^ E_DEPRECATED);
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0];
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $x = 30;

        $imagePath = 'logo.png';
        if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath(
                $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)
            );
            $page->drawImage($image, 20, 800, 150, 830);
        }

        $pdfStorePhone = $this->scopeConfig->getValue('general/store_information/phone', ScopeInterface::SCOPE_STORE) ?: '';
        $pdfSalesEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE) ?: '';

        $order = $templateVars['order'];
        $orderid = $order->getIncrementId();
        $leftX = $x;
        $rightX = $x + 300;

        $y3 = 770;
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Appointment Confirmation of Order #" . $orderid), $x, $y3, 'UTF-8');

        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);
        $page->drawText(__("Dear Customer,"), $x, $y3 - 20, 'UTF-8');
        $page->drawText(__("Please see the confirmation for the following updates to your order."), $x, $y3 - 40, 'UTF-8');

        $sectionY = $y3 - 80;

        // Billing Info
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Billing Information"), $leftX, $sectionY, 'UTF-8');
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);

        $billingAddress = $order->getBillingAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $billingCountry = $objectManager->create(\Magento\Directory\Model\Country::class)->load($billingAddress->getCountryId());

        $page->drawText($billingAddress->getFirstname() . " " . $billingAddress->getLastname(), $leftX, $sectionY - 15, 'UTF-8');
        $page->drawText(implode(", ", $billingAddress->getStreet()), $leftX, $sectionY - 30, 'UTF-8');
        $page->drawText($billingAddress->getCity() . " " . $billingAddress->getPostcode(), $leftX, $sectionY - 45, 'UTF-8');
        $page->drawText($billingCountry->getName(), $leftX, $sectionY - 60, 'UTF-8');
        $page->drawText("T: " . $billingAddress->getTelephone(), $leftX, $sectionY - 75, 'UTF-8');
        $page->drawText("Email: " . $order->getCustomerEmail(), $leftX, $sectionY - 90, 'UTF-8');

        // Installer Info
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Installer Information"), $rightX, $sectionY, 'UTF-8');
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);

        $page->drawText($templateVars['installer_name'], $rightX, $sectionY - 15, 'UTF-8');
        $page->drawText($templateVars['installer_street'], $rightX, $sectionY - 30, 'UTF-8');
        $page->drawText($templateVars['installer_city'], $rightX, $sectionY - 45, 'UTF-8');
        $page->drawText($templateVars['installer_country'], $rightX, $sectionY - 60, 'UTF-8');
        $page->drawText($templateVars['installer_phone'], $rightX, $sectionY - 75, 'UTF-8');

        // Second row
        $rowY = $sectionY - 120;

        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Date & Time"), $leftX, $rowY, 'UTF-8');
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);
        $page->drawText($templateVars['admin_installer_date'], $leftX, $rowY - 15, 'UTF-8');

        // Order Updates (left column, below Date & Time)
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Order Updates"), $leftX, $rowY - 40, 'UTF-8');
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);
        $commentWrapped = wordwrap($templateVars['admin_installer_comment'], 55, "\n", false);
        $commentLines = explode("\n", $commentWrapped);
        $lineY = $rowY - 55;
        foreach ($commentLines as $cLine) {
            $page->drawText($cLine, $leftX, $lineY, 'UTF-8');
            $lineY -= 14;
        }

        // Vehicle Information (right column)
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Vehicle Information"), $rightX, $rowY, 'UTF-8');
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);
        $page->drawText("Plate No.: " . $order->getPlate(), $rightX, $rowY - 15, 'UTF-8');
        $page->drawText("Make: " . $order->getMake(), $rightX, $rowY - 30, 'UTF-8');
        $page->drawText("Model: " . $order->getModel(), $rightX, $rowY - 45, 'UTF-8');
        $page->drawText("Year: " . $order->getYear(), $rightX, $rowY - 60, 'UTF-8');

        // Determine cursor position from whichever column is lower
        $vehicleBottomY = $rowY - 75;
        $lineY = min($lineY, $vehicleBottomY);

        $cursorY = $lineY - 15;

        // Items table
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->drawRectangle($x, $cursorY, $x + 520, $cursorY - 20);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(255, 255, 255));
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__('ITEM'), $x + 10, $cursorY - 13, 'UTF-8');
        $page->drawText(__('QTY'), $x + 470, $cursorY - 13, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);

        $topy = $cursorY - 40;
        foreach ($order->getAllItems() as $item) {
            $page->drawText($item->getName(), $x + 10, $topy, 'UTF-8');
            $page->drawText((int) $item->getQtyOrdered(), $x + 470, $topy, 'UTF-8');
            $topy -= 20;
        }

        // Notes
        $yy3 = $topy - 20;
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $page->drawText(__("Please note the following,"), $x, $yy3, 'UTF-8');
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);
        $yy3 -= 20;

        $drawWrapped = function ($page, $text, $x, $y, $maxChars = 127, $lineHeight = 15) {
            $lines = explode("\n", wordwrap($text, $maxChars, "\n", true));
            foreach ($lines as $line) {
                $page->drawText($line, $x, $y, 'UTF-8');
                $y -= $lineHeight;
            }
            return $y;
        };

        $yy3 = $drawWrapped($page,
            "1. The tyres supplied to Installer Partner under this work order will be fitted ONLY to the Vehicle bearing the license plate number and the description specified here. Please contact Customer Service if there is a discrepancy here.",
            $x, $yy3);
        $yy3 -= 5;
        $yy3 = $drawWrapped($page,
            "2. Please refuse to accept delivery if the tyres being installed by the installer are not the exact same specifications as listed here and call us immediately.",
            $x, $yy3);
        $yy3 -= 5;
        $yy3 = $drawWrapped($page,
            "3. The price you have paid includes new tyres, delivery, installation, balancing and disposal of your old tyres. Any additional Service Including Alignment will have to be paid by you to the installer directly. We do recommend you align your wheel at least once a year.",
            $x, $yy3);
        $yy3 -= 5;
        $drawWrapped($page,
            "Please feel free to call us at " . $pdfStorePhone . " or Write to us at " . $pdfSalesEmail,
            $x, $yy3);

        $pdfData = $pdf->render();

        $appointmentdir = $this->dir->getPath('media') . '/appointment';
        if (!file_exists($appointmentdir)) {
            mkdir($appointmentdir, 0775, true);
        }

        $fileName = 'appointment_' . $orderid . '_' . time() . '.pdf';
        $appointmentpath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'appointment/' . $fileName;
        file_put_contents($appointmentpath, $pdfData);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return [
            'filename' => $fileName,
            'pdfData' => $pdfData,
            'pdfdownload' => $mediaUrl . 'appointment/' . $fileName
        ];
    }

}