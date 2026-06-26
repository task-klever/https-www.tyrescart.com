<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class Downloadpdf extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $scopeConfig;
    protected $pickupstores;
    protected $storeManagerInterface;
    protected $country;
    protected $_filesystem;
    protected $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomteck\StoreLocator\Model\Stores $pickupstores,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Directory\Model\Country $country,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->_order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->pickupstores = $pickupstores;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->country = $country;
        $this->_filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $orderId = $this->getRequest()->getParam('id');
        }
        $date = $this->getRequest()->getParam('installer_date');
        $comment = $this->getRequest()->getParam('installer_comment');
        $type = $this->getRequest()->getParam('type');

        if ($type === 'appointment' && empty(trim($comment))) {
            $comment = 'Please present this to the Installer Partner to verify and proceed with the fitment.';
        }

        $order = $this->_order->load($orderId);

        if (!$order->getId()) {
            $this->messageManager->addErrorMessage(__('Order not found.'));
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            return;
        }

        // Save date/comment to DB
        if ($type === 'workorder') {
            $order->setWorkorderDatetime($date);
            $order->setWorkorderComment($comment);
        } else {
            $order->setAppointmentDatetime($date);
            $order->setAppointmentComment($comment);
        }
        $order->save();

        $installerId = $order->getPickupStore();
        if (empty($installerId)) {
            $this->messageManager->addErrorMessage(__('Installer not found for this order.'));
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            return;
        }

        $pickupstoresData = $this->pickupstores->load($installerId);
        $storeManager = $this->storeManagerInterface;
        $storeManager->setCurrentStore($order->getStore()->getId());
        $countryName = $this->country->load($pickupstoresData->getCountry())->getName();

        $vehicleVinNo = $order->getVinNumber() ?: '';

        $templateVars = [
            'order' => $order,
            'admin_installer_date' => $date,
            'admin_installer_comment' => $comment,
            'installer_name' => $pickupstoresData->getName(),
            'installer_street' => $pickupstoresData->getAddress(),
            'installer_city' => $pickupstoresData->getCity(),
            'installer_region' => $pickupstoresData->getRegion(),
            'installer_country' => $countryName,
            'installer_email' => $pickupstoresData->getEmail(),
            'installer_phone' => 'T: ' . $pickupstoresData->getPhone(),
            'vehicle_plate' => $order->getPlate(),
            'vehicle_vinno' => $vehicleVinNo,
            'vehicle_make' => $order->getMake(),
            'vehicle_model' => $order->getModel(),
            'vehicle_year' => $order->getYear(),
            'customer_name' => $order->getCustomerName(),
        ];

        try {
            if ($type === 'workorder') {
                $pdf = $this->generateWorkOrderPdf($templateVars);
                $fileName = 'WorkOrder_' . $order->getIncrementId() . '.pdf';
            } else {
                $pdf = $this->generateAppointmentPdf($templateVars);
                $fileName = 'Appointment_' . $order->getIncrementId() . '.pdf';
            }

            return $this->fileFactory->create(
                $fileName,
                $pdf->render(),
                DirectoryList::MEDIA,
                'application/pdf'
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error generating PDF: %1', $e->getMessage()));
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        }
    }

    private function generateWorkOrderPdf($templateVars)
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
        $this->y = 850 - 170;

        $imagePath = 'logo.png';
        if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath(
                $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)
            );
            $page->drawImage($image, 20, 800, 150, 830);
        }

        $pdfStoreName = $this->scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE) ?: '';
        $pdfStorePhone = $this->scopeConfig->getValue('general/store_information/phone', ScopeInterface::SCOPE_STORE) ?: '';
        $pdfSalesEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE) ?: '';

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText('New Work Order', $x, $this->y + 90, 'UTF-8');

        $order = $templateVars['order'];
        $orderid = $order->getIncrementId();

        $y3 = 740;
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

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__($order->getCustomerName()), $x, $y3 - 95, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Installer Information"), $x + 300, $y3 - 125, 'UTF-8');

        $street = wordwrap($templateVars['installer_street'], 50, "&&");
        $street = explode('&&', $street);
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText($templateVars['installer_name'], $x + 300, $y3 - 140, 'UTF-8');
        $streetbreak = 155;
        foreach ($street as $value) {
            $page->drawText($value, $x + 300, $y3 - $streetbreak, 'UTF-8');
            $streetbreak += 15;
        }
        $page->drawText($templateVars['installer_city'], $x + 300, $y3 - $streetbreak, 'UTF-8');
        $streetbreak += 15;
        $page->drawText($templateVars['installer_country'], $x + 300, $y3 - $streetbreak, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Vehicle Information"), $x, $y3 - 125, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Plate No.: " . $order->getPlate()), $x, $y3 - 140, 'UTF-8');
        $page->drawText(__("Make: " . $order->getMake()), $x, $y3 - 155, 'UTF-8');
        $page->drawText(__("Model: " . $order->getModel()), $x, $y3 - 170, 'UTF-8');
        $page->drawText(__("Year: " . $order->getYear()), $x, $y3 - 185, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Delivery Date/Time :"), $x, $y3 - 215, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText($templateVars['admin_installer_date'], $x + 100, $y3 - 215, 'UTF-8');

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Order Updates"), $x, $y3 - 235, 'UTF-8');
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);

        $commentWrapped = wordwrap($templateVars['admin_installer_comment'], 115, "\n", false);
        $commentLines = explode("\n", $commentWrapped);
        $commentY = $y3 - 255;
        foreach ($commentLines as $cLine) {
            $page->drawText($cLine, $x, $commentY, 'UTF-8');
            $commentY -= 14;
        }

        $cursorY = $commentY - 15;

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->drawRectangle($x, $cursorY, $x + 520, $cursorY - 20);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(255, 255, 255));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('ITEM'), $x + 10, $cursorY - 13, 'UTF-8');
        $page->drawText(__('QTY'), $x + 470, $cursorY - 13, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $cursorY -= 20;

        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            $cursorY -= 20;
            $page->drawText($item->getName(), $x + 10, $cursorY, 'UTF-8');
            $page->drawText((int) $item->getQtyOrdered(), $x + 470, $cursorY, 'UTF-8');
        }

        $cursorY -= 30;
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("Please note the following,"), $x, $cursorY, 'UTF-8');
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 10);
        $page->setStyle($style);
        $cursorY -= 20;
        $page->drawText(__("1. The tyres supplied to you under this work order must be fitted ONLY to the Vehicle bearing the license plate number and "), $x, $cursorY, 'UTF-8');
        $cursorY -= 15;
        $page->drawText(__("description specified here. Please contact us if the customer insists on fitting these to another vehicle. "), $x, $cursorY, 'UTF-8');
        $cursorY -= 20;
        $page->drawText(__("2. Please refuse to accept delivery if the tyres being delivered by the courier are not the exact same specifications and DOT as listed here"), $x, $cursorY, 'UTF-8');
        $cursorY -= 20;
        $page->drawText(__("3. The customer has paid for new tyres, delivery, installation, balancing and disposal of old tyres.You will have to charges the customer"), $x, $cursorY, 'UTF-8');
        $cursorY -= 15;
        $page->drawText(__(" directly for Alignment or any other service you provide."), $x, $cursorY, 'UTF-8');
        $cursorY -= 20;
        $page->drawText(__("4. The work order will not be considered complete unless the customer sign this work order and you email back a scanned copy to"), $x, $cursorY, 'UTF-8');
        $cursorY -= 15;
        $page->drawText(__(" your " . $pdfStoreName . " contact."), $x, $cursorY, 'UTF-8');
        $cursorY -= 30;
        $page->drawText(__("Please feel free to call us at " . $pdfStorePhone . " or Write to us at " . $pdfSalesEmail), $x, $cursorY, 'UTF-8');
        $cursorY -= 30;
        $page->drawText(__("Job Complete (Y/N):_________"), $x, $cursorY, 'UTF-8');
        $page->drawText(__("Remarks (if any):_____________"), $x + 300, $cursorY, 'UTF-8');
        $cursorY -= 30;
        $page->drawText(__("Date & Time:_______________"), $x, $cursorY, 'UTF-8');
        $page->drawText(__("Customer Signature:__________"), $x + 300, $cursorY, 'UTF-8');

        return $pdf;
    }

    private function generateAppointmentPdf($templateVars)
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

        return $pdf;
    }
}
