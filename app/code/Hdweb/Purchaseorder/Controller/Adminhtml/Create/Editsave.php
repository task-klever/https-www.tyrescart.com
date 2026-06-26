<?php

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Editsave extends \Magento\Backend\App\Action
{

    protected $resultPagee;
    protected $purchaseorder;
    protected $purchaseorderitem;
    protected $povendor;
    protected $order;
    protected $scopeConfig;
    protected $productRepository;
    protected $pricehelper;
    protected $_filesystem;
    protected $fileFactory;
    protected $authSession;
    protected $orderInterfaceFactory;
    protected $orderItemFactory;
    protected $pohelper;
    private $objectManager;
    protected $addressConfig;
    protected $ecomtechStoreLocator;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $storeManager;
    protected $countryModel;

    protected $resultPageFactory;
    protected $y;

    public function __construct(
        Context $context, PageFactory $resultPageFactory,
        \Hdweb\Purchaseorder\Model\PurchaseorderFactory $purchaseorder,
        \Hdweb\Purchaseorder\Model\PurchaseorderitemFactory $purchaseorderitem,
        \Hdweb\Purchaseorder\Model\Povendor $povendor,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricehelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        Session $authSession,
        \Hdweb\Purchaseorder\Helper\Data $pohelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Ecomteck\StoreLocator\Model\Stores $ecomtechStoreLocator,
        \Hdweb\Core\Model\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Country $countryModel

    ) {
        parent::__construct($context);
        $this->resultPageFactory     = $resultPageFactory;
        $this->purchaseorder         = $purchaseorder;
        $this->purchaseorderitem     = $purchaseorderitem;
        $this->povendor              = $povendor;
        $this->order                 = $order;
        $this->scopeConfig           = $scopeConfig;
        $this->productRepository     = $productRepository;
        $this->pricehelper           = $pricehelper;
        $this->_filesystem           = $filesystem;
        $this->fileFactory           = $fileFactory;
        $this->authSession           = $authSession;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderItemFactory      = $orderItemFactory;
        $this->pohelper              = $pohelper;
        $this->objectManager = $objectmanager;
        $this->addressConfig = $addressConfig;
        $this->ecomtechStoreLocator = $ecomtechStoreLocator;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->countryModel = $countryModel;
    }

    public function execute()
    {

        $data           = $this->_request->getParams();
        $submit_param   = $data['submit'];
        if (isset($submit_param) && $submit_param != 'delete') {
            if ($data['po_type'] == 'mpo') {
                $notAllowedSkus = [];
                $redirectBack = false;
                foreach ($data['item'] as $key => $poProductData) {
                    if ($poProductData['qty'] > $poProductData['allowedqty']) {
                        $order_incrementid = $data['orderreference_no'];
                        $order = $this->order->loadByIncrementId($order_incrementid);
                        $notAllowedSkus[] = $poProductData['sku'];
                        $redirectBack = true;       
                    }
                }
                if ($redirectBack) {
                    $notAllowedSkusString = implode(', ', $notAllowedSkus);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $params = array('po_id' => $data['poid']);
                    $this->messageManager->addError(__('You are entering more than allowed qty for SKU - '.$notAllowedSkusString));
                    return $resultRedirect->setPath('purchaseorder/create/edit', $params);
                }
            }

            if ($data['po_type'] == 'fpo') {
                $notAllowedSkus = [];
                $redirectBack = false;
                foreach ($data['item'] as $key => $poProductData) {
                    if(isset($poProductData['qty']) && isset($poProductData['allowedqty'])){
                        if ($poProductData['qty'] > $poProductData['allowedqty']) {
                            $order_incrementid = $data['orderreference_no'];
                            $order = $this->order->loadByIncrementId($order_incrementid);
                            $notAllowedSkus[] = $poProductData['sku'];
                            $redirectBack = true;       
                        }
                    }
                }
                if ($redirectBack) {
                    $notAllowedSkusString = implode(', ', $notAllowedSkus);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $params = array('po_id' => $data['poid']);
                    $this->messageManager->addError(__('You are entering more than allowed qty for SKU - '.$notAllowedSkusString));
                    return $resultRedirect->setPath('purchaseorder/create/edit', $params);
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $submit_param   = $data['submit'];
        if (isset($submit_param) && $submit_param == 'pdf') {           $sendEmailpdf = $this->sendmailpdf();
            $this->messageManager->addSuccess(__('Purchase order email has been sent succesfully'));
            $params = array('po_id' => $data['poid']);
            return $resultRedirect->setPath('purchaseorder/create/edit', $params);
        } else if (isset($submit_param) && $submit_param == 'download') {
            return $sendEmailpdf = $this->sendmailpdf();
            $this->messageManager->addSuccess(__('Purchasee order has been downloaded'));
            return $resultRedirect->setPath('*/*/grid');
        } else if (isset($submit_param) && $submit_param == 'download_delivery') {
            return $this->downloadDeliveryNote();
        } else if (isset($submit_param) && $submit_param == 'delete') {
            if (isset($data['poid'])) {
                //
                $purchaseorder_model = $this->purchaseorder->create();
                $purchaseorder_model->load($data['poid'], 'id');
                $purchaseorder_model->delete();
                $ispurchaseorderdone = 0;
                if (isset($data['item']) && count($data['item']) > 0) {
                    $model                       = $this->purchaseorderitem->create();
                    $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $data['poid']);

                    foreach ($purchaseorderitemcollection as $modeldata) {
                        $ispurchaseorderdone = 1;
                        $model->load($modeldata['id'], 'id');
                        $model->delete();
                    }
                }

                if ($ispurchaseorderdone) {
                    $this->pohelper->savePoGrandTotal($data['orderreference_no']);
                }
                $this->messageManager->addSuccess(__('Your purchase order has been deleted succesfully'));
                return $resultRedirect->setPath('*/*/grid');
            }
        } else {

            if (isset($data['item']) && isset($data['poid'])) {

                if (isset($data['grandtotal']) && count($data['item']) > 0 && $data['grandtotal'] > 0) {
                    $itemtemplate        = "";
                    $ispurchaseorderdone = 0;

                    $purchaseorder_model = $this->purchaseorder->create();
                    $purchaseorder_model->load($data['poid'], 'id');
                    $purchaseorder_model->setPoreferenceNo($data['poreference_no']);
                    $purchaseorder_model->setOrderreferenceNo($data['orderreference_no']);
                    $vendorId = $data['vendor'];
                    $purchaseorder_model->setVendor($vendorId);
                    
                    $vendorCollection = $this->povendor->getCollection();
                    $vendorCollection->addFieldToFilter('id', array('eq' => $vendorId));
                    $vendorName = '';
                    if ($vendorCollection->getSize()) {
                        $vendorData = $vendorCollection->getFirstItem();
                        $vendorName = $vendorData->getName();
                    }
                    
                    $purchaseorder_model->setVendorName($vendorName);
                    $purchaseorder_model->setSubtotal($data['subtotal']);
                    $purchaseorder_model->setVat($data['vat']);
                    $purchaseorder_model->setGrandtotal($data['grandtotal']);
                    $purchaseorder_model->setComment($data['comment']);
                    $purchaseorder_model->setPickupLocation($data['pickup_location'] ?? '');
                    $purchaseorder_model->setDropoffLocation($data['dropoff_location'] ?? '');
                    $purchaseorder_model->setUpdateBy($this->authSession->getUser()->getId());
                    $purchaseorder_model->save();
                    $last_po_id = $purchaseorder_model->getId();

                    if (count($data['item']) > 0) {

                        $model = $this->purchaseorderitem->create();

                        $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $data['poid']);

                        foreach ($purchaseorderitemcollection as $modeldata) {

                            $model->load($modeldata['id'], 'id');
                            $model->delete();
                        }

                        foreach ($data['item'] as $key => $value) {

                            $CreatedAt    = date('Y-m-d h:i:s', time());
                            $product_objs = $this->productRepository->get($value['sku']);

                            $ispurchaseorderdone     = 1;
                            $purchaseorderitem_model = $this->purchaseorderitem->create();
                            $purchaseorderitem_model->setPoid($last_po_id);
                            $purchaseorderitem_model->setPoreferenceNo($data['poreference_no']);
                            $purchaseorderitem_model->setPoType($data['po_type']);
                            $purchaseorderitem_model->setSku($value['sku']);
                            $purchaseorderitem_model->setPrice($value['price']);
                            $purchaseorderitem_model->setQty($value['qty']);

                            $purchaseorderitem_model->setVendorId($vendorId);
                            $purchaseorderitem_model->setVendorName($vendorName);
                            $purchaseorderitem_model->setOrderId($data['orderreference_no']);
                            $purchaseorderitem_model->setCreatedAt($CreatedAt);
                            $purchaseorderitem_model->setTyreDescription($product_objs->getName());

                            $rowtotal = trim($value['price']) * (int) $value['qty'];
                            $rowtotal = number_format($rowtotal, 2);
                            $rowtotal = str_replace(',', '', $rowtotal);

                            $purchaseorderitem_model->setRowtotal($rowtotal);
                            $purchaseorderitem_model->save();
                            $purchaseorderitem_model->unsetData();

                            //for email template

                            $orderref   = $this->orderInterfaceFactory->create()->loadByIncrementId($data['orderreference_no']);
                            $orderitems = $this->orderItemFactory->create()->getCollection()->addFieldToFilter('order_id', array('eq' => $orderref->getEntityId()))->addFieldToFilter('sku', array('eq' => $value['sku']))->getFirstItem();

                            $itemtemplate .= '<tr>
                                                       <td colspan="3"><span style="padding-top:5px;font-weight:100;font-size:14px;">
                                                       <br>' . $orderitems->getShortDescription() . '</span><br>
                                                          SKU: ' . $value['sku'] . ' </span>
                                                       </td>
                                                            <td style="text-align:center;font-size:14px;">' . $this->pricehelper->currency($value['price'], true, false) . '</td>
                                                            <td style="text-align:center;font-size:14px;">' . $value['qty'] . '</td>
                                                            <td style="text-align:center;font-size:14px;">
                                                                <span class="price">' . $this->pricehelper->currency($rowtotal, true, false) . '</span>
                                                            </td>
                                                  </tr>';
                        }
                    }

                    if ($ispurchaseorderdone) {
                        $this->pohelper->savePoGrandTotal($data['orderreference_no']);
                    }

                    /*email done */
                    $this->messageManager->addSuccess(__('Your purchase order has been edited successfully'));
                    
                    //return $resultRedirect->setPath('*/*/grid');
                    $params = array('po_id' => $data['poid']);
                    return $resultRedirect->setPath('purchaseorder/create/edit', $params);

                } else {
                    $this->messageManager->addError(__('Faild to create purchase order.'));
                    
                    return $resultRedirect->setPath('*/*/grid');
                }

            } else {
                /*email done */
                $this->messageManager->addError(__('No any product item found.'));
                
                return $resultRedirect->setPath('*/*/grid');
            }
        }
    }

    public function sendmailpdf()
    {

        /* Send email*/
        $data           = $this->_request->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $itemtemplate   = "";
        $model          = $this->purchaseorderitem->create();

        $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $data['poid']);

        foreach ($data['item'] as $key => $value) {

            $cleanPrice = preg_replace('/[^0-9.]/', '', $value['price']);
            $numericPrice = (float) $cleanPrice;
            $qty = (int) $value['qty'];
            $rowtotal = $numericPrice * $qty;
            $rowtotal = number_format($rowtotal, 2, '.', '');

/*           $rowtotal = trim($value['price']) * (int) $value['qty'];
            $rowtotal = number_format($rowtotal, 2);
            $rowtotal = str_replace(',', '', $rowtotal);*/

            //for email template
            $product_obj = $this->productRepository->get($value['sku']);
            
            $itemtemplate .= '<tr>
                                                       <td colspan="3"><span style="padding-top:5px;font-weight:100;font-size:14px;">
                                                       <br>' . $product_obj->getName() . '</span><br>
                                                          SKU: ' . $value['sku'] . ' </span>
                                                       </td>
                                                            <td style="text-align:right;font-size:14px;">' . $this->pricehelper->currency($value['price'], true, false) . '</td>
                                                            <td style="text-align:center;font-size:14px;">' . $value['qty'] . '</td>
                                                            <td style="text-align:right;font-size:14px;">
                                                                <span class="price">' . $this->pricehelper->currency($rowtotal, true, false) . '</span>
                                                            </td>
                                                  </tr>';

        }
        /* Send email*/

        $vendorid   = $data['vendor'];
        $collection = $this->povendor->getCollection();
        $collection->addFieldToFilter('id', array('eq' => $vendorid));

        if ($collection->getSize()) {
            $vendor_data = $collection->getFirstItem();
            $vendor_name          = $vendor_data->getName();
            $vendor_contactperson = $vendor_data->getContactPerson();
            $vendor_email         = $vendor_data->getEmail();
            $vendor_copy_email    = $vendor_data->getEmailCopy();
            $vendor_phone         = $vendor_data->getPhone();
            $vendor_whatsapp_number         = $vendor_data->getWhatsappNumber();
            $vendor_address       = $vendor_data->getAddress();
            $vendor_city          = $vendor_data->getCity();

            $bill_to = $vendor_name . "<br>" . $vendor_address . "<br>" . $vendor_contactperson . "<br>Tel: " . $vendor_phone . "<br>Email: " . $vendor_email;

            $ordercomment = $data['comment'];

            $poreference_no = $data['poreference_no'];

            $order_incrementid = $data['orderreference_no'];
            $order = $this->order->loadByIncrementId($order_incrementid);
            
            $installer_id = $order->getPickupStore();            

            if($installer_id != 0){
                $installer_id = $installer_id;
            }else{
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            }
            $installerobj = $this->ecomtechStoreLocator->load($installer_id);

            $shipingAddress     = $order->getShippingAddress();
            $renderer           = $this->addressConfig->getFormatByCode('html')->getRenderer();
            $installer_country          = $this->countryModel->load($installerobj['country'])->getName();


            $installer_section = "<p>" . $installerobj['name'] . "<br>" . $installerobj['address'] . "<br>" . $installerobj['region'] . "<br>" . $installer_country . "<br> Phone: " . $installerobj['phone'] . "<br> Email: " . $installerobj['email'] . "<br> <a href='" . $installerobj->getExternalLink() . "'>Location Map</a> </p>";
            
            $podate           = date("d/m/Y");
            $itemtable = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <thead class="thead-dark" style="background-color:#000;border-bottom-color:#000;color:white;">
                                          <tr>
                                                <th scope="col" colspan="3" style="font-weight: 100;font-size: 14px;padding:10px 9px;">Item</th>
                                                <th scope="col" style="text-align:center;font-weight: 100;font-size: 14px;padding:10px 9px;">Price</th>
                                                <th scope="col" style="text-align:center;font-weight: 100;font-size: 14px;padding:10px 9px;">Qty</th>
                                                <th scope="col" style="text-align:center;font-weight: 100;font-size: 14px;padding:10px 9px;">Subtotal</th>
                                              </tr>
                                        </thead>
                                       <tbody>' . $itemtemplate . '</tbody>
                                        <tfoot class="order-totals">
                                                <tr class="subtotal" style="text-align: right;background: #fff;">
                                                    <td colspan="5" scope="row" style="background: #fff !important;">
                                                                    Sub Total
                                                    </td>
                                                    <td data-td="Sub Total" style="text-align: right;background: #fff !important;">
                                                             <span class="price">' . $this->pricehelper->currency($data['subtotal'], true, false) . '</span>
                                                    </td>
                                                </tr>

                                                <tr class="totals-tax">
                                                    <td colspan="5" scope="row" style="background: #fff !important;text-align:right;">
                                                                    VAT(5%)            </td>
                                                    <td data-th="VAT(5%)" style="background: #fff !important;text-align:right;">
                                                        <span class="price">' . $this->pricehelper->currency($data['vat'], true, false) . '</span>    </td>
                                                </tr>


                                              <tr class="grand_total" style="text-align: right;background: #fff;">
                                                     <td colspan="5" scope="row" style="background: #fff !important;">
                                                                Grand Total(Incl. VAT)
                                                     </td>
                                                    <td data-td="Grand Total(Incl. VAT)" style="text-align: right;background: #fff !important;">
                                                                <span class="price">' . $this->pricehelper->currency($data['grandtotal'], true, false) . '</span>
                                                     </td>
                                              </tr>
                                        </tfoot>
                            </table>';

                /* Create PDF */
                $pdflogoName            = $this->scopeConfig->getValue('purchaseorder/general/po_logo_name', ScopeInterface::SCOPE_STORE);
                $pdfstoreName           = $this->scopeConfig->getValue('purchaseorder/general/po_store_name', ScopeInterface::SCOPE_STORE);
                $pdfStoreaddressStreet1 = $this->scopeConfig->getValue('purchaseorder/general/po_store_address_street1', ScopeInterface::SCOPE_STORE);
                $pdfStoreaddressStreet2 = $this->scopeConfig->getValue('purchaseorder/general/po_store_address_street2', ScopeInterface::SCOPE_STORE);
                $pdfTrnno               = $this->scopeConfig->getValue('purchaseorder/general/po_trn_no', ScopeInterface::SCOPE_STORE);
                $pdfPhoneno             = $this->scopeConfig->getValue('purchaseorder/general/po_phone_no', ScopeInterface::SCOPE_STORE);
                $pdfWebsiteName         = $this->scopeConfig->getValue('purchaseorder/general/po_website', ScopeInterface::SCOPE_STORE);
                $pdfContactPerson       = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person', ScopeInterface::SCOPE_STORE);
                $pdfContactPersonPhone  = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person_phone_no', ScopeInterface::SCOPE_STORE);
                $pdfContactPersonEmail  = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person_email', ScopeInterface::SCOPE_STORE);
                $pdfFileName            = $this->scopeConfig->getValue('purchaseorder/general/po_file_name', ScopeInterface::SCOPE_STORE);
                $pdf                    = new \Zend_Pdf();
                $pdf->pages[]           = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                $page                   = $pdf->pages[0]; // this will get reference to the first page.
                $style                  = new \Zend_Pdf_Style();
                $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
                $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
                $style->setFont($font, 15);
                $page->setStyle($style);
                $width        = $page->getWidth();
                $hight        = $page->getHeight();
                $x            = 30;
                $pageTopalign = 850; //default PDF page height
                $this->y      = 850 - 170; //print table row from page top – 100px
                //Draw table header row’s
                $style->setFont($font, 16);
                $page->setStyle($style);

                $imagePath = 'logo.png';
                if ($pdflogoName != '') {
                    $imagePath = $pdflogoName;
                }

                $storeName          = '';
                $storeAddress1      = '';
                $storeAddress2      = '';
                $trnNo              = '';
                $phoneNo            = '';
                $website            = '';
                $contactPerson      = '';
                $contactPersonPhone = '';
                $contactPersonEmail = '';
                $pdfFile            = 'PO-';

                if ($pdfstoreName != '') {
                    $storeName = $pdfstoreName;
                }

                if ($pdfStoreaddressStreet1 != '') {
                    $storeAddress1 = $pdfStoreaddressStreet1;
                }

                if ($pdfStoreaddressStreet2 != '') {
                    $storeAddress2 = $pdfStoreaddressStreet2;
                }

                if ($pdfTrnno != '') {
                    $trnNo = $pdfTrnno;
                }
                if ($pdfPhoneno != '') {
                    $phoneNo = $pdfPhoneno;
                }
                if ($pdfWebsiteName != '') {
                    $website = $pdfWebsiteName;
                }
                if ($pdfContactPerson != '') {
                    $contactPerson = $pdfContactPerson;
                }
                if ($pdfContactPersonPhone != '') {
                    $contactPersonPhone = $pdfContactPersonPhone;
                }
                if ($pdfContactPersonEmail != '') {
                    $contactPersonEmail = $pdfContactPersonEmail;
                }
                if ($pdfFileName != '') {
                    $pdfFile = $pdfFileName;
                }

                $image = "";
                if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)) {
                    $image = \Zend_Pdf_Image::imageWithPath($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath));
                }

                $y1 = 800;
                $y2 = 830;
                $x1 = 400;
                $x2 = 530;

                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $page->drawRectangle(30, $this->y - 20, $page->getWidth() - 30, $this->y + 110, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $style->setFont($font, 10);
                $page->setStyle($style);


                // $page->drawText(__($storeName), $x + 5, $this->y + 90, 'UTF-8');
                // $style->setFont($font, 10);
                // $page->setStyle($style);
                // $page->drawText(__($storeAddress1), $x + 5, $this->y + 75, 'UTF-8');
                // $page->drawText(__($storeAddress2), $x + 5, $this->y + 60, 'UTF-8');
                // $page->drawText(__("TRN: " . $trnNo), $x + 5, $this->y + 45, 'UTF-8');
                // $page->drawText(__("Phone: " . $phoneNo), $x + 5, $this->y + 30, 'UTF-8');
                // $page->drawText(__("Website: " . $website), $x + 5, $this->y + 15, 'UTF-8');

                $currentY = $this->y + 90;
                $lineSpacing = 15; // Adjust as needed

                if ($storeName != '') {
                    $page->drawText(__($storeName), $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                $style->setFont($font, 10);
                $page->setStyle($style);

                if ($storeAddress1 != '') {
                    $page->drawText(__($storeAddress1), $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                if ($storeAddress2 != '') {
                    $page->drawText(__($storeAddress2), $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                if ($trnNo != '') {
                    $page->drawText(__("TRN: " . $trnNo), $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                if ($phoneNo != '') {
                    $page->drawText(__("Phone: " . $phoneNo), $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                if ($website != '') {
                    $page->drawText(__("Website: " . $website), $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                $page->drawText(__("PURCHASE ORDER"), $x + 350, $this->y + 90, 'UTF-8');
                $page->drawText(__("DATE"), $x + 350, $this->y + 75, 'UTF-8');
                $page->drawText(__("PO/ORDER #"), $x + 350, $this->y + 60, 'UTF-8');

                //Po value
                $page->drawText(date("d/m/Y"), $x + 450, $this->y + 75, 'UTF-8');
                $page->drawText($data['orderreference_no'], $x + 450, $this->y + 60, 'UTF-8');
                //$page->drawText($data['orderreference_no'], $x + 430, $this->y+10, 'UTF-8');

                // Vendor Detail
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 30);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $style->setFont($font, 10);
                $page->drawText(__('VENDOR'), $x + 5, $this->y - 18, 'UTF-8');
                $page->drawText(__('SHIP TO'), 300, $this->y - 18, 'UTF-8');

                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $style->setFont($font, 10);

                $currentY = $this->y - 50; // Start Y after vendor_name

                if ($vendor_name != '') {
                    $page->drawText($vendor_name, $x + 5, $currentY, 'UTF-8');
                    $currentY -= 20; // Move down for next block
                }

                $lineSpacing = 20;

                if ($vendor_address != '') {
                    $maxCharsPerLine = 50;
                    $lines = str_split($vendor_address, $maxCharsPerLine);
                    
                    foreach ($lines as $line) {
                        $page->drawText($line, $x + 5, $currentY, 'UTF-8');
                        $currentY -= $lineSpacing;
                    }
                }

                if ($vendor_phone != '') {
                    $page->drawText('Phone: ' . $vendor_phone, $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }

                if ($vendor_email != '') {
                    $page->drawText('Email: ' . $vendor_email, $x + 5, $currentY, 'UTF-8');
                    $currentY -= $lineSpacing;
                }
                
            $billingAddress = $order->getShippingAddress();

                $customerAddressLines = [];
                $customerPhone = '';
                $customerEmail = '';

                if ($billingAddress) {
                    $customerAddressLines[] = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
                    if ($billingAddress->getCompany()) {
                        $customerAddressLines[] = $billingAddress->getCompany();
                    }
                    $customerAddressLines[] = $billingAddress->getStreetLine(1);
                    if ($billingAddress->getStreetLine(2)) {
                        $customerAddressLines[] = $billingAddress->getStreetLine(2);
                    }
                    $customerAddressLines[] = $billingAddress->getCity();
                    $customerAddressLines[] = $billingAddress->getRegion();


                    $customerPhone = $billingAddress->getTelephone();
                    $customerEmail = $order->getCustomerEmail();
                }

                $stringobj = $this->objectManager->get(\Magento\Framework\Stdlib\StringUtils::class);
                $startY = $this->y - 50;

                if (!empty($installerobj['address'])) {
                    // --- INSTALLER ADDRESS ---
                    if (!empty($installerobj['name'])) {
                        $page->drawText($installerobj['name'], 300, $startY, 'UTF-8');
                        $startY -= 15;
                    }

                    $values = explode("\n", $installerobj['address']);
                    foreach ($values as $value) {
                        if ($value !== '') {
                            $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                            foreach ($stringobj->split($value, 50, true, true) as $_value) {
                                $page->drawText(trim(strip_tags($_value)), 300, $startY, 'UTF-8');
                                $startY -= 15;
                            }
                        }
                    }

                    if (!empty($installerobj['city'])) {
                        $page->drawText($installerobj['city'], 300, $startY, 'UTF-8');
                        $startY -= 15;
                    }

                    if (!empty($installerobj['phone'])) {
                        $page->drawText('Phone: ' . $installerobj['phone'], 300, $startY, 'UTF-8');
                        $startY -= 15;
                    }

                    if (!empty($installerobj['email'])) {
                        $page->drawText('Email: ' . $installerobj['email'], 300, $startY, 'UTF-8');
                        $startY -= 15;
                    }

                } else {
                    // --- CUSTOMER ADDRESS ---
                    foreach ($customerAddressLines as $line) {
                        foreach ($stringobj->split($line, 50, true, true) as $_line) {
                            $page->drawText(trim($_line), 300, $startY, 'UTF-8');
                            $startY -= 15;
                        }
                    }

                    if (!empty($customerPhone)) {
                        $page->drawText('Phone: ' . $customerPhone, 300, $startY, 'UTF-8');
                        $startY -= 15;
                    }

                    if (!empty($customerEmail)) {
                        $page->drawText('Email: ' . $customerEmail, 300, $startY, 'UTF-8');
                        $startY -= 15;
                    }
                }                

                $cursorY = $this->y - 160;

                if ($data['comment'] != '') {
                    $page->drawText(__('Comments: '), $x + 5, $cursorY, 'UTF-8');

                    $comment = wordwrap($data['comment'], 90, "\n", false);
                    $lines = explode("\n", $comment);
                    $lineY = $cursorY;
                    $lineHeight = 12;

                    foreach ($lines as $line) {
                        $page->drawText($line, 100, $lineY, 'UTF-8');
                        $lineY -= $lineHeight;
                    }
                    $cursorY = $lineY - 15;
                } else {
                    $cursorY = $cursorY - 20;
                }

                // Items header
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->drawRectangle(30, $cursorY, $page->getWidth() - 30, $cursorY - 20);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $style->setFont($font, 10);
                $page->drawText(__('ITEM'), $x + 5, $cursorY - 15, 'UTF-8');
                $page->drawText(__('DESCRIPTION'), 130, $cursorY - 15, 'UTF-8');
                $page->drawText(__('QTY'), 360, $cursorY - 15, 'UTF-8');
                $page->drawText(__('UNIT PRICE'), 410, $cursorY - 15, 'UTF-8');
                $page->drawText(__('TOTAL'), 500, $cursorY - 15, 'UTF-8');

                $cursorY = $cursorY - 20;

                // Item values
                $style->setFont($font, 10);
                $itemY = $cursorY - 18;
                foreach ($data['item'] as $key => $value) {
                    $itemno      = $key + 1;
                    $product_obj = $this->productRepository->get($value['sku']);
/*                    $rowtotal    = trim($value['price']) * (int) $value['qty'];
                    $rowtotal    = number_format($rowtotal, 2);
                    $rowtotal    = str_replace(',', '', $rowtotal);
*/
                    $cleanPrice = preg_replace('/[^0-9.]/', '', $value['price']);
                    $numericPrice = (float) $cleanPrice;
                    $qty = (int) $value['qty'];
                    $rowtotal = $numericPrice * $qty;
                    $rowtotal = number_format($rowtotal, 2, '.', '');
                                

                    $page->drawText($itemno, 40, $itemY, 'UTF-8');
                    $page->drawText($product_obj->getName(), 80, $itemY, 'UTF-8');
                    $page->drawText($product_obj->getSku(), 80, $itemY - 15, 'UTF-8');
                    $page->drawText($value['qty'], 360, $itemY, 'UTF-8');
                    $page->drawText($this->pricehelper->currency($numericPrice, true, false), 410, $itemY, 'UTF-8');
                    $page->drawText($this->pricehelper->currency($rowtotal, true, false), 490, $itemY, 'UTF-8');

                    $itemY -= 30;
                }

                $cursorY = $itemY;

                // Subtotal
                $page->drawText(__('SUB TOTAL'), 400, $cursorY, 'UTF-8');
                $page->drawText($this->pricehelper->currency($data['subtotal'], true, false), 490, $cursorY, 'UTF-8');

                $cursorY -= 20;
                $page->drawText(__('TAX'), 400, $cursorY, 'UTF-8');
                $page->drawText($this->pricehelper->currency($data['vat'], true, false), 490, $cursorY, 'UTF-8');

                $cursorY -= 20;
                $page->drawText(__('GRAND TOTAL'), 400, $cursorY, 'UTF-8');
                $page->drawText($this->pricehelper->currency($data['grandtotal'], true, false), 490, $cursorY, 'UTF-8');

                // Instructions header
                $cursorY -= 20;
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->drawRectangle(30, $cursorY, $page->getWidth() - 30, $cursorY - 20);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $style->setFont($font, 10);
                $page->drawText(__('COMMMENT OR SPECIAL INSTRUCTIONS'), 40, $cursorY - 15, 'UTF-8');

                $instructionTop = $cursorY - 20;
                $page->drawText(__('1. Please mention this Order number in your invoices for this order.'), 40, $instructionTop - 15, 'UTF-8');
                $page->drawText(__('2. Please notify us immediately if you are unable to ship as specified.'), 40, $instructionTop - 30, 'UTF-8');
                $page->drawText(__('3. The tyre/s to be supplied under this purchase order must comply with UAE law and with standards'), 40, $instructionTop - 45, 'UTF-8');
                $page->drawText(__('approved as per Gulf Technical Regulations by GSO.'), 50, $instructionTop - 60, 'UTF-8');

                $instructionBottom = $instructionTop - 70;
                $page->drawRectangle(30, $instructionTop, $page->getWidth() - 30, $instructionBottom, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

                // Footer
                $cursorY = $instructionBottom - 20;
                $page->drawText(__('If you have any questions about this purchase order, please contact'), 150, $cursorY, 'UTF-8');
                $page->drawText(__('[' . $contactPerson . ', ' . $contactPersonPhone . ', Email: ' . $contactPersonEmail . ']'), 150, $cursorY - 15, 'UTF-8');

                $fileName = $pdfFile . $data['orderreference_no'] . '.pdf';
                $pdfData  = $pdf->render(); // Get PDF document as a string

                if ($data['submit'] == 'download') {
                    return $this->fileFactory->create(
                        $fileName,
                        $pdf->render(),
                        \Magento\Framework\App\Filesystem\DirectoryList::MEDIA, // this pdf will be saved in var directory with the name example.pdf
                        'application/octet-stream'
                    );

                }

                if ($data['submit'] != 'download') {
                    /* Create PDF end*/
                    $fileName = $data['orderreference_no'] . '_' . time() . '.pdf';
                    $popath   = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'po/' . $fileName;
                    file_put_contents($popath, $pdf->render());

                    $this->storeManager->setCurrentStore($order->getStore()->getId());
                    $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
                    $mediaUrl        = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    $pdfdownload     = $mediaUrl . 'po/' . $fileName;
                    $templateVars    = array(
                        'poreference_no'    => $poreference_no,
                        'bill_to'           => $bill_to,
                        'ordercomment'      => $ordercomment,
                        'order_incrementid' => $order_incrementid,
                        'installer_section'           => $installer_section,
                        'itemtable'         => $itemtable,
                        'podate'            => $podate,
                        'pdfdownload'       => $pdfdownload,
                        'ship_to'       => $installer_section,
                    );
                    $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
                    $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);

                    $copy_to = $this->scopeConfig->getValue('sales_email/order/copy_to', ScopeInterface::SCOPE_STORE);

                    $from = array('email' => $email, 'name' => $name);
                    $this->inlineTranslation->suspend();
                    
                    $emailTemplateId = $this->scopeConfig->getValue('purchaseorder/general/po_email_template_id', ScopeInterface::SCOPE_STORE);
                    if ($emailTemplateId != '') {
                        if($vendor_copy_email != ''){
                            $vendor_copy_email = explode(',', $vendor_copy_email);
                        }
                        if ($vendor_data->getEmailCopy()) {
                            $transport = $this->transportBuilder->setTemplateIdentifier($emailTemplateId)
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($templateVars)
                                ->setFrom($from)
                                ->addTo($vendor_email) // $vendor_email
                                ->addBcc($vendor_copy_email)
                                ->addAttachment($pdfData, $fileName, 'application/pdf')
                                ->getTransport();
                        } else {
                            $transport = $this->transportBuilder->setTemplateIdentifier($emailTemplateId)
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($templateVars)
                                ->setFrom($from)
                                ->addTo($vendor_email) // $vendor_email
                                ->getTransport();
                        }

                        $transport->sendMessage();
                        $this->inlineTranslation->resume();
                    } else {
                        $this->messageManager->addError(__('Purchase Order Email Template not configured yet!.'));
                    }
                    
                    /* Start WhatsApp Notification */
                    if($vendor_whatsapp_number){
                        $postData = array('poreference_no' => $poreference_no, 'vendor_phone' => $vendor_whatsapp_number, 'comment' => $ordercomment, 'pdf_link' => $pdfdownload);
                        $responsFinal = $this->objectManager->create('Hdweb\Rfc\Helper\Data')->sendWhatsAppNotification($order, $templateId = 56684, $notifyInstaller = null, $notifyCustomer = null, $orderUpdateComment = null, $po = true, $postData);   
                    }
                    /* End WhatsApp Notification */
                }
                $adminUser = $this->authSession->getUser();
                $orderPoComment = 'PO is sent via email to '.$vendor_name . ' - BY ' . $adminUser->getFirstname(). ' '.$adminUser->getLastname();
                $order->addStatusHistoryComment($orderPoComment);
                $order->save();
        }
    }

    public function getpdf()
    {

        $pdf          = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page         = $pdf->pages[0]; // this will get reference to the first page.
        $style        = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width        = $page->getWidth();
        $hight        = $page->getHeight();
        $x            = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y      = 850 - 150; //print table row from page top – 100px
        //Draw table header row’s
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->drawRectangle(30, $this->y - 20, $page->getWidth() - 30, $this->y + 90, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 15);
        $page->setStyle($style);

        $imagePath = 'logo.png';
        $image     = "";
        if ($this->_mediaDirectory->isFile($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));

        }

        $y1 = 800;
        $y2 = 830;
        $x1 = 400;
        $x2 = 530;

        $page->drawImage($image, $x1, $y1, $x2, $y2);

        $page->drawText(__("Tyres Vision"), $x + 5, $this->y + 70, 'UTF-8');
        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("Aspin Commercial Tower,"), $x + 5, $this->y + 55, 'UTF-8');
        $page->drawText(__("20th floor, Sheikh Zayed Road, Dubai"), $x + 5, $this->y + 40, 'UTF-8');
        $page->drawText(__("Phone:01 234 5678"), $x + 5, $this->y + 25, 'UTF-8');
        $page->drawText(__("Website: www.tyresvision.com "), $x + 5, $this->y + 10, 'UTF-8');

        $page->drawText(__("PURCHASE ORDER"), $x + 350, $this->y + 70, 'UTF-8');
        $page->drawText(__("DATE"), $x + 350, $this->y + 50, 'UTF-8');
        $page->drawText(__("PO/ORDER #"), $x + 350, $this->y + 30, 'UTF-8');
        $page->drawText(__("TRN #"), $x + 350, $this->y + 10, 'UTF-8');

        //Po value
        $page->drawText(__("6/9/12"), $x + 430, $this->y + 50, 'UTF-8');
        $page->drawText($data['poreference_no'], $x + 430, $this->y + 30, 'UTF-8');
        $page->drawText($data['orderreference_no'], $x + 430, $this->y + 10, 'UTF-8');

        // Vendor Detail
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 30);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 14);
        $page->drawText(__('VENDOR'), 40, $this->y - 18, 'UTF-8');
        $page->drawText(__('SHIP TO:'), 300, $this->y - 18, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);

        $page->drawText(__('Devvedra patel'), 40, $this->y - 50, 'UTF-8');
        $page->drawText(__('T:34343434243'), 40, $this->y - 70, 'UTF-8');
        $page->drawText(__('Email:test@gmail.com'), 40, $this->y - 90, 'UTF-8');

        $page->drawText(__('Devvedra patel'), 300, $this->y - 50, 'UTF-8');
        $page->drawText(__('Installer Address'), 300, $this->y - 70, 'UTF-8');
        $page->drawText(__('Installer Contact Person'), 300, $this->y - 90, 'UTF-8');
        $page->drawText(__('T:34343434243'), 300, $this->y - 110, 'UTF-8');

        $page->drawText(__('Comment:'), 40, $this->y - 170, 'UTF-8');
        $page->drawText(__('Test comment'), 100, $this->y - 170, 'UTF-8');

        // iTems

        // Vendor Detail
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y - 200, $page->getWidth() - 30, $this->y - 220);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 14);
        $page->drawText(__('ITEM #'), 40, $this->y - 215, 'UTF-8');
        $page->drawText(__('DESCRIPTION'), 130, $this->y - 215, 'UTF-8');
        $page->drawText(__('QTY'), 320, $this->y - 215, 'UTF-8');
        $page->drawText(__('UNIT PRICE'), 370, $this->y - 215, 'UTF-8');
        $page->drawText(__('TOTAL'), 500, $this->y - 215, 'UTF-8');

        //ITEM VALUE
        $style->setFont($font, 12);
        $page->drawText(__('1'), 40, $this->y - 245, 'UTF-8');
        $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 245, 'UTF-8');
        $page->drawText(__('2'), 330, $this->y - 245, 'UTF-8');
        $page->drawText(__('AED 1200'), 370, $this->y - 245, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 245, 'UTF-8');

        $page->drawText(__('2'), 40, $this->y - 270, 'UTF-8');
        $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 270, 'UTF-8');
        $page->drawText(__('2'), 330, $this->y - 270, 'UTF-8');
        $page->drawText(__('AED 1200'), 370, $this->y - 270, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 270, 'UTF-8');

        $page->drawText(__('3'), 40, $this->y - 300, 'UTF-8');
        $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 300, 'UTF-8');
        $page->drawText(__('2'), 330, $this->y - 300, 'UTF-8');
        $page->drawText(__('AED 1200'), 370, $this->y - 300, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 300, 'UTF-8');

        //subtotal

        $page->drawText(__('SUBTOTAL'), 400, $this->y - 330, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 330, 'UTF-8');

        $page->drawText(__('TAX'), 400, $this->y - 360, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 360, 'UTF-8');

        $page->drawText(__('GRAND TOTAL'), 400, $this->y - 390, 'UTF-8');
        $page->drawText(__('AED 3600'), 500, $this->y - 390, 'UTF-8');

        // commment and instruction

        $page->drawRectangle(30, $this->y, $page->getWidth() - 30, $this->y - 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y - 420, $page->getWidth() - 30, $this->y - 440);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 14);
        $page->drawText(__('COMMMENT OR SPECIAL INSTRUCTIONS'), 40, $this->y - 435, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle(30, $this->y - 440, $page->getWidth() - 30, $this->y - 510, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);

        $page->drawText(__('1. Please mention this Order number in your invoices for this order'), 40, $this->y - 460, 'UTF-8');

        $page->drawText(__('2. Please notify us immediately if you are unable to ship as specified'), 40, $this->y - 475, 'UTF-8');

        $page->drawText(__('3. The tyre/s to be supplied under this purchase order must comply with UAE law and with standards
            '), 40, $this->y - 490, 'UTF-8');

        $page->drawText(__('approved as Gulf Technical Regulations by GSO'), 50, $this->y - 505, 'UTF-8');

        // footer

        $page->drawText(__('If you have any questions about this purchase order, please contact'), 150, $this->y - 540, 'UTF-8');
        $page->drawText(__('[Mr. Devendra, 0543473401 or Email: devendra.it@live.com]'), 130, $this->y - 560, 'UTF-8');

        $fileName = 'example.pdf';

        $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name example.pdf
            'application/pdf'
        );
    }

    public function downloadDeliveryNote()
    {
        $data = $this->_request->getParams();

        // Load PO data
        $purchaseorder_model = $this->purchaseorder->create();
        $purchaseorder_model->load($data['poid'], 'id');
        $poData = $purchaseorder_model->getData();

        // Load items from the referenced MPO (parent PO) if reference_po exists, otherwise load DPO's own items
        $referencePo = $poData['reference_po'] ?? null;
        $itemPoid = $referencePo ?: $data['poid'];
        $purchaseorderitemcollection = $this->purchaseorderitem->create()->getCollection()->addFieldToFilter('poid', $itemPoid);

        // Load order
        $order_incrementid = $poData['orderreference_no'];
        $order = $this->order->loadByIncrementId($order_incrementid);

        // Get config values (same as sendmailpdf)
        $pdflogoName            = $this->scopeConfig->getValue('purchaseorder/general/po_logo_name', ScopeInterface::SCOPE_STORE);
        $pdfstoreName           = $this->scopeConfig->getValue('purchaseorder/general/po_store_name', ScopeInterface::SCOPE_STORE);
        $pdfStoreaddressStreet1 = $this->scopeConfig->getValue('purchaseorder/general/po_store_address_street1', ScopeInterface::SCOPE_STORE);
        $pdfStoreaddressStreet2 = $this->scopeConfig->getValue('purchaseorder/general/po_store_address_street2', ScopeInterface::SCOPE_STORE);
        $pdfTrnno               = $this->scopeConfig->getValue('purchaseorder/general/po_trn_no', ScopeInterface::SCOPE_STORE);
        $pdfPhoneno             = $this->scopeConfig->getValue('purchaseorder/general/po_phone_no', ScopeInterface::SCOPE_STORE);
        $pdfWebsiteName         = $this->scopeConfig->getValue('purchaseorder/general/po_website', ScopeInterface::SCOPE_STORE);
        $pdfContactPerson       = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person', ScopeInterface::SCOPE_STORE);
        $pdfContactPersonPhone  = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person_phone_no', ScopeInterface::SCOPE_STORE);
        $pdfContactPersonEmail  = $this->scopeConfig->getValue('purchaseorder/general/po_contact_person_email', ScopeInterface::SCOPE_STORE);
        $pdfFileName            = $this->scopeConfig->getValue('purchaseorder/general/po_file_name', ScopeInterface::SCOPE_STORE);

        $storeName = $pdfstoreName ?: '';
        $storeAddress1 = $pdfStoreaddressStreet1 ?: '';
        $storeAddress2 = $pdfStoreaddressStreet2 ?: '';
        $phoneNo = $pdfPhoneno ?: '';
        $website = $pdfWebsiteName ?: '';
        $contactPerson = $pdfContactPerson ?: '';
        $contactPersonPhone = $pdfContactPersonPhone ?: '';
        $contactPersonEmail = $pdfContactPersonEmail ?: '';
        $pdfFile = $pdfFileName ?: 'PO-';

        // Create PDF
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0];
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $boldFont = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES_BOLD);
        $style->setFont($font, 16);
        $page->setStyle($style);
        $x = 30;
        $this->y = 850 - 170;

        // Logo
        $imagePath = 'logo.png';
        if ($pdflogoName != '') {
            $imagePath = $pdflogoName;
        }
        if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath));
            $page->drawImage($image, 400, 800, 530, 830);
        }

        // Count store detail lines for dynamic box height
        $storeLineCount = 0;
        if ($storeName != '') $storeLineCount++;
        if ($storeAddress1 != '') $storeLineCount++;
        if ($storeAddress2 != '') $storeLineCount++;
        if ($phoneNo != '') $storeLineCount++;
        if ($website != '') $storeLineCount++;

        // Right side needs 4 lines (title, date, po/order#, po no.), left needs storeLineCount
        $maxLines = max($storeLineCount, 4);
        $lineSpacing = 15;
        $boxTopPadding = 10;
        $boxBottomPadding = 10;
        $headerBoxHeight = $boxTopPadding + ($maxLines * $lineSpacing) + $boxBottomPadding;
        $headerBoxTop = $this->y - 20 + $headerBoxHeight;

        // Header box (dynamic height)
        $page->drawRectangle($x, $this->y - 20, $page->getWidth() - $x, $headerBoxTop, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 10);
        $page->setStyle($style);

        // Store details on left (dynamic, skip empty)
        $currentY = $headerBoxTop - $boxTopPadding - 5;

        if ($storeName != '') {
            $page->drawText(__($storeName), $x + 5, $currentY, 'UTF-8');
            $currentY -= $lineSpacing;
        }
        if ($storeAddress1 != '') {
            $page->drawText(__($storeAddress1), $x + 5, $currentY, 'UTF-8');
            $currentY -= $lineSpacing;
        }
        if ($storeAddress2 != '') {
            $page->drawText(__($storeAddress2), $x + 5, $currentY, 'UTF-8');
            $currentY -= $lineSpacing;
        }
        if ($phoneNo != '') {
            $page->drawText(__("Phone: " . $phoneNo), $x + 5, $currentY, 'UTF-8');
            $currentY -= $lineSpacing;
        }
        if ($website != '') {
            $page->drawText(__("Website: " . $website), $x + 5, $currentY, 'UTF-8');
            $currentY -= $lineSpacing;
        }

        // Right side: DELIVERY PICKUP NOTE + DATE + PO/ORDER #
        $rightY = $headerBoxTop - $boxTopPadding - 5;
        $style->setFont($boldFont, 12);
        $page->setStyle($style);
        $page->drawText(__("DELIVERY PICKUP NOTE"), $x + 350, $rightY, 'UTF-8');
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__("DATE:") . '  ' . date("d/m/Y"), $x + 350, $rightY - $lineSpacing, 'UTF-8');
        $page->drawText(__("PO/ORDER #:") . '  ' . $order_incrementid, $x + 350, $rightY - ($lineSpacing * 2), 'UTF-8');
        $page->drawText(__("PO No.:") . '  ' . $poData['poreference_no'], $x + 350, $rightY - ($lineSpacing * 3), 'UTF-8');

        $cursorY = $this->y - 30;

        // PICKUP LOCATION / DROP OFF LOCATION
        $pickupLocation = $poData['pickup_location'] ?? '';
        $dropoffLocation = $poData['dropoff_location'] ?? '';

        $maxCharsPerLine = 45;
        $pickupLines = $pickupLocation ? explode("\n", wordwrap($pickupLocation, $maxCharsPerLine, "\n", true)) : [''];
        $dropoffLines = $dropoffLocation ? explode("\n", wordwrap($dropoffLocation, $maxCharsPerLine, "\n", true)) : [''];
        $maxLines = max(count($pickupLines), count($dropoffLines));
        $locationBoxHeight = max(80, 40 + ($maxLines * 15));

        // Grey header bar for locations
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle($x, $cursorY, $page->getWidth() - $x, $cursorY - 25);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('PICKUP LOCATION'), $x + 5, $cursorY - 17, 'UTF-8');
        $page->drawText(__('DROP OFF LOCATION'), 300, $cursorY - 17, 'UTF-8');

        // Location box
        $page->drawRectangle($x, $cursorY, $page->getWidth() - $x, $cursorY - $locationBoxHeight, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 10);
        $page->setStyle($style);

        // Pickup text
        $lineY = $cursorY - 42;
        foreach ($pickupLines as $line) {
            $page->drawText($line, $x + 5, $lineY, 'UTF-8');
            $lineY -= 15;
        }

        // Dropoff text
        $lineY = $cursorY - 42;
        foreach ($dropoffLines as $line) {
            $page->drawText($line, 300, $lineY, 'UTF-8');
            $lineY -= 15;
        }

        $cursorY = $cursorY - $locationBoxHeight;

        // Comments section
        $comment = $poData['comment'] ?? '';
        if ($comment != '') {
            $page->drawText(__('Comments: '), $x + 5, $cursorY - 15, 'UTF-8');
            $commentWrapped = wordwrap($comment, 90, "\n", true);
            $commentLines = explode("\n", $commentWrapped);
            $cLineY = $cursorY - 15;
            foreach ($commentLines as $cLine) {
                $page->drawText($cLine, 100, $cLineY, 'UTF-8');
                $cLineY -= 12;
            }
            $cursorY = $cLineY - 10;
        } else {
            $cursorY = $cursorY - 10;
        }

        // Items table header
        $itemsHeaderTop = $cursorY;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle($x, $cursorY, $page->getWidth() - $x, $cursorY - 20);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('DESCRIPTION'), $x + 5, $cursorY - 15, 'UTF-8');
        $page->drawText(__('QTY'), 480, $cursorY - 15, 'UTF-8');

        $cursorY = $cursorY - 20;

        // Items
        $style->setFont($font, 10);
        $page->setStyle($style);
        $itemY = $cursorY - 18;
        foreach ($purchaseorderitemcollection as $item) {
            $description = $item->getTyreDescription() ?: $item->getSku();
            $page->drawText($description, $x + 5, $itemY, 'UTF-8');
            $page->drawText($item->getQty(), 485, $itemY, 'UTF-8');
            $itemY -= 20;
        }

        $cursorY = $itemY - 5;

        // Instructions section
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle($x, $cursorY, $page->getWidth() - $x, $cursorY - 20);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('COMMENTS OR SPECIAL INSTRUCTIONS'), $x + 5, $cursorY - 15, 'UTF-8');

        $instructionBoxTop = $cursorY - 20;

        $page->drawText(__('1. The driver is authorized to pick up the listed tyres from the warehouse on behalf of TyresCart.'), $x + 5, $instructionBoxTop - 15, 'UTF-8');
        $page->drawText(__('2. Please ensure the tyres are verified for correct specifications and quantity before handover.'), $x + 5, $instructionBoxTop - 30, 'UTF-8');
        $page->drawText(__('3. Any shortage or damage must be reported immediately at the time of pickup.'), $x + 5, $instructionBoxTop - 45, 'UTF-8');

        $instructionBoxBottom = $instructionBoxTop - 55;
        $page->drawRectangle($x, $instructionBoxTop, $page->getWidth() - $x, $instructionBoxBottom, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

        $cursorY = $instructionBoxBottom;

        // Footer contact text
        $footerY = $cursorY - 25;
        $page->drawText(__('If you have any questions about this purchase order, please contact'), 150, $footerY, 'UTF-8');
        $page->drawText(__('[' . $contactPerson . ', ' . $contactPersonPhone . ', Email: ' . $contactPersonEmail . ']'), 150, $footerY - 15, 'UTF-8');

        // Signature sections - side by side
        $sigY = $footerY - 45;
        $style->setFont($boldFont, 10);
        $page->setStyle($style);

        // Left signature
        $page->drawText(__('Tyres picked up from warehouse'), $x + 5, $sigY, 'UTF-8');
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('Date & Time:_______________'), $x + 5, $sigY - 20, 'UTF-8');

        // Right signature
        $style->setFont($boldFont, 10);
        $page->setStyle($style);
        $page->drawText(__('Tyres delivered to destination'), 320, $sigY, 'UTF-8');
        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText(__('Date & Time:_______________'), 320, $sigY - 20, 'UTF-8');

        // Generate PDF
        $fileName = $pdfFile . $data['orderreference_no'] . '-delivery-note.pdf';

        return $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
            'application/octet-stream'
        );
    }

}
