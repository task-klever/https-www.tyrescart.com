<?php
/**
 * Custom Invoice PDF – overrides "Sold to:" and "Ship to:" labels.
 */
declare(strict_types=1);

namespace Klever\OrderActions\Model\Order\Pdf;

use Ecomteck\StoreLocator\Model\ResourceModel\Stores\CollectionFactory as StoresCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Pdf\Invoice as MagentoInvoice;
use Magento\Sales\Model\RtlTextHandler;
use Magento\Tax\Helper\Data as TaxHelper;

class Invoice extends MagentoInvoice
{
    /**
     * @var RtlTextHandler
     */
    private $kleverRtlTextHandler;

    /**
     * @var TaxHelper
     */
    private $kleverTaxHelper;

    /**
     * @var StoresCollectionFactory
     */
    private $storesCollectionFactory;

    /**
     * Insert order to pdf page.
     *
     * Overridden to change:
     *   "Sold to:" → "Customer Details:"
     *   "Ship to:" → "Shipping/Fitment Details:"
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Model\Order\Shipment $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if (!$this->kleverRtlTextHandler) {
            $this->kleverRtlTextHandler = ObjectManager::getInstance()->get(RtlTextHandler::class);
        }
        if (!$this->kleverTaxHelper) {
            $this->kleverTaxHelper = ObjectManager::getInstance()->get(TaxHelper::class);
        }
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        /* White header rectangle (no gray background) */
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top, 570, $top - 70);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 70]);

        /* "Tax Invoice" centered bold 18pt */
        $this->_setFontBold($page, 18);
        $page->drawText(__('Tax Invoice'), 250, $top - 25, 'UTF-8');

        /* Invoice # (right side) */
        $this->_setFontRegular($page, 10);
        $invoice = $order->getInvoiceCollection()->setPageSize(1)->getFirstItem();
        $invoiceIncrementId = $invoice && $invoice->getId() ? $invoice->getIncrementId() : 'N/A';
        $page->drawText(__('Invoice # ') . $invoiceIncrementId, 440, $top - 40, 'UTF-8');

        /* Invoice Date (right side, below Invoice #) */
        $invoiceCreatedAt = ($invoice && $invoice->getId() && $invoice->getCreatedAt())
            ? $invoice->getCreatedAt()
            : $order->getCreatedAt();
        $page->drawText(
            __('Invoice Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $invoiceCreatedAt,
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            440,
            $top - 57,
            'UTF-8'
        );

        $top -= 80;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 298, $top - 25);
        $page->drawRectangle(298, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = $paymentInfo !== null ? htmlspecialchars_decode($paymentInfo, ENT_QUOTES) : '';
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if ($value && strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            $shippingDesc = $order->getShippingDescription();

            if ($shippingDesc === 'Mobile Van Service') {
                /* Mobile Van: show only "Mobile Van Fitment" */
                $shippingAddress = ['Mobile Van Fitment'];
            } elseif ($shippingDesc === 'Delivery - Without Fitment') {
                /* Free shipping / no fitment: show same as billing (Customer Details) */
                $shippingAddress = $billingAddress;
            } else {
                /* Install at Outlet (default): load installer store data directly */
                $shippingAddress = $this->_getInstallerAddress($order);
            }

            $shippingMethod = $shippingDesc;
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        /* Changed: "Sold to:" → "Customer Details:" */
        $page->drawText(__('Customer Details:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            /* Changed: "Ship to:" → "Shipping/Fitment Details:" */
            $page->drawText(__('Shipping/Fitment Details:'), 308, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 308, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $this->kleverRtlTextHandler->reverseRtlText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part ?: '')), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            $shippingAddress = $shippingAddress ?? []; // @phpstan-ignore-line
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $this->kleverRtlTextHandler->reverseRtlText($_value);
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part ?: '')), 308, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 298, $this->y - 25);
            $page->drawRectangle(298, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Vehicle Information:'), 308, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if ($value && trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value ?: '')), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            /* Vehicle Information */
            $vehicleLines = [];
            $plate = $order->getPlate();
            $make  = $order->getMake();
            $model = $order->getModel();
            $year  = $order->getYear();
            if ($plate) {
                $vehicleLines[] = __('Plate No.') . ': ' . $plate;
            }
            if ($make) {
                $vehicleLines[] = __('Make') . ': ' . $make;
            }
            if ($model) {
                $vehicleLines[] = __('Model') . ': ' . $model;
            }
            if ($year) {
                $vehicleLines[] = __('Year') . ': ' . $year;
            }
            if (empty($vehicleLines)) {
                $vehicleLines[] = __('N/A');
            }
            foreach ($vehicleLines as $_line) {
                $page->drawText(strip_tags(trim((string)$_line)), 308, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yVehicle = $this->y;

            $currentY = min($yPayments, $yVehicle);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Draw header for item table.
     *
     * Overridden: custom columns without SKU.
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        $lines[0][] = ['text' => __('Description'), 'feed' => 35];
        $lines[0][] = ['text' => __('Price'), 'feed' => 395, 'align' => 'right'];
        $lines[0][] = ['text' => __('Qty'), 'feed' => 435, 'align' => 'right'];
        $lines[0][] = ['text' => __('VAT (5%)'), 'feed' => 495, 'align' => 'right'];
        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Load installer store data by pickup_store ID and build address lines.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function _getInstallerAddress($order)
    {
        $pickupStoreId = (int)$order->getPickupStore();
        if ($pickupStoreId > 0) {
            try {
                if (!$this->storesCollectionFactory) {
                    $this->storesCollectionFactory = ObjectManager::getInstance()
                        ->get(StoresCollectionFactory::class);
                }
                $collection = $this->storesCollectionFactory->create();
                $collection->addFieldToFilter('stores_id', $pickupStoreId);
                $collection->setPageSize(1);
                $storeData = $collection->getFirstItem()->getData();

                if (!empty($storeData['stores_id'])) {
                    $lines = [];
                    if (!empty($storeData['name'])) {
                        $lines[] = $storeData['name'];
                    }
                    if (!empty($storeData['address'])) {
                        $lines[] = $storeData['address'];
                    }
                    $cityRegionPostcode = trim(
                        (!empty($storeData['city']) ? $storeData['city'] : '')
                        . (!empty($storeData['region']) ? ', ' . $storeData['region'] : '')
                        . (!empty($storeData['postcode']) ? ' ' . $storeData['postcode'] : '')
                    );
                    if ($cityRegionPostcode) {
                        $lines[] = $cityRegionPostcode;
                    }
                    if (!empty($storeData['phone'])) {
                        $lines[] = 'T: ' . $storeData['phone'];
                    }
                    if (!empty($lines)) {
                        return $lines;
                    }
                }
            } catch (\Exception $e) {
                // fall through to default
            }
        }

        // Fallback: use formatted shipping address
        return $this->_formatAddress(
            $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
        );
    }
}
