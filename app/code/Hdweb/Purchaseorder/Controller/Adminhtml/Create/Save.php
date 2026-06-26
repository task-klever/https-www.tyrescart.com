<?php

namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Save extends \Magento\Backend\App\Action
{

    protected $resultPagee;
    protected $purchaseorder;
    protected $purchaseorderitem;
    protected $povendor;
    protected $order;
    protected $scopeConfig;
    protected $productRepository;
    protected $pricehelper;
    protected $date;
    protected $_scopeConfig;
    protected $authSession;
    protected $pohelper;

    protected $resultPageFactory;

    public function __construct(
        Context $context, PageFactory $resultPageFactory,
        \Hdweb\Purchaseorder\Model\Purchaseorder $purchaseorder,
        \Hdweb\Purchaseorder\Model\Purchaseorderitem $purchaseorderitem,
        \Hdweb\Purchaseorder\Model\Povendor $povendor,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricehelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Session $authSession,
        \Hdweb\Purchaseorder\Helper\Data $pohelper

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->purchaseorder     = $purchaseorder;
        $this->purchaseorderitem = $purchaseorderitem;
        $this->povendor          = $povendor;
        $this->order             = $order;
        $this->scopeConfig       = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->pricehelper       = $pricehelper;
        $this->date              = $date;
        $this->authSession       = $authSession;
        $this->pohelper          = $pohelper;

    }

    public function execute()
    {

        $data = $this->_request->getParams();

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
                $params = array('order_id' => $order->getId(),'po_type' => 'mpo');
                $this->messageManager->addError(__('You are entering more than allowed qty for SKU - '.$notAllowedSkusString));
                return $resultRedirect->setPath('purchaseorder/create/index', $params);
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
                $params = array('order_id' => $order->getId(),'po_type' => 'fpo','fpo' => 1);
                $this->messageManager->addError(__('You are entering more than allowed qty for SKU - '.$notAllowedSkusString));
                return $resultRedirect->setPath('purchaseorder/create/index', $params);
            }
        }
        
        

        $resultRedirect = $this->resultRedirectFactory->create();
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $objDate        = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');

        if (isset($data['item'])) {

            if (isset($data['grandtotal']) && count($data['item']) > 0 && $data['grandtotal'] > 0) {
                $itemtemplate        = "";
                $currentime          = time();
                $ispurchaseorderdone = 0;

                $purchaseorder_model = $this->purchaseorder;
                $purchaseorder_model->setPoreferenceNo($data['poreference_no']);
                $purchaseorder_model->setOrderreferenceNo($data['orderreference_no']);
                
                $vendorId = $data['vendor'];
                $vendorCollection = $this->povendor->getCollection();
                $vendorCollection->addFieldToFilter('id', array('eq' => $vendorId));
                $vendorName = '';
                if ($vendorCollection->getSize()) {
                    $vendorData = $vendorCollection->getFirstItem();
                    $vendorName = $vendorData->getName();
                }
                $purchaseorder_model->setVendor($vendorId);
                $purchaseorder_model->setVendorName($vendorName);
                $purchaseorder_model->setSubtotal($data['subtotal']);
                $purchaseorder_model->setVat($data['vat']);
                $purchaseorder_model->setGrandtotal($data['grandtotal']);
                $purchaseorder_model->setComment($data['comment']);
                $purchaseorder_model->setPickupLocation($data['pickup_location'] ?? '');
                $purchaseorder_model->setDropoffLocation($data['dropoff_location'] ?? '');
                $purchaseorder_model->setReferencePo($data['reference_po'] ?? null);
                $purchaseorder_model->setDate($currentime);
                $purchaseorder_model->setPoType($data['po_type']);
                $purchaseorder_model->setCreateBy($this->authSession->getUser()->getId());
                $purchaseorder_model->save();
                $last_po_id = $purchaseorder_model->getId();

                if (count($data['item']) > 0) {
                    $totalPoQty = 0;
                    foreach ($data['item'] as $key => $value) {
                        $CreatedAt = date('Y-m-d h:i:s', time());
                        //for email template
                        $product_obj = $this->productRepository->get($value['sku']);

                        $ispurchaseorderdone     = 1;
                        $purchaseorderitem_model = $this->purchaseorderitem;
                        $purchaseorderitem_model->setPoid($last_po_id);
                        $purchaseorderitem_model->setPoreferenceNo($data['poreference_no']);
                        $purchaseorderitem_model->setSku($value['sku']);
                        $purchaseorderitem_model->setPrice($value['price']);
                        $purchaseorderitem_model->setQty($value['qty']);

                        $purchaseorderitem_model->setVendorId($vendorId);
                        $purchaseorderitem_model->setVendorName($vendorName);
                        $purchaseorderitem_model->setOrderId($data['orderreference_no']);
                        $purchaseorderitem_model->setCreatedAt($CreatedAt);
                        $purchaseorderitem_model->setTyreDescription($product_obj->getName());
                        $purchaseorderitem_model->setPoType($data['po_type']);

                        $rowtotal = (int) $value['price'] * (int) $value['qty'];
                        $rowtotal = number_format($rowtotal, 2);
                        $rowtotal = str_replace(',', '', $rowtotal);
                        $purchaseorderitem_model->setRowtotal($rowtotal);
                        $purchaseorderitem_model->save();
                        $purchaseorderitem_model->unsetData();

                        $itemtemplate .= '<tr>
                                             <td colspan="3"><span style="padding-top:5px;font-weight:500;">
                                             <br>' . $product_obj->getName() . '</span><br>
                                                SKU: ' . $value['sku'] . ' </span>
                                             </td>
                                                  <td style="text-align:center;font-size:14px;">' . $this->pricehelper->currency($value['price'], true, false) . '</td>
                                                  <td style="text-align:center;font-size:14px;">' . $value['qty'] . '</td>
                                                  <td style="text-align:center;font-size:14px;">
                                                      <span class="price">' . $this->pricehelper->currency($rowtotal, true, false) . '</span>
                                                  </td>
                                        </tr>';
                        $totalPoQty +=  $value['qty'];
                    }
                }

                if ($ispurchaseorderdone) {
                    $this->pohelper->savePoGrandTotal($data['orderreference_no']);
                }

                /* Send email*/
                $vendorid   = $data['vendor'];
                $collection = $this->povendor->getCollection();
                $collection->addFieldToFilter('id', array('eq' => $vendorid));

                if ($collection->getSize()) {
                    $vendor_data          = $collection->getFirstItem();
                    $vendor_name          = $vendor_data->getName();
                    $vendor_contactperson = $vendor_data->getContactPerson();
                    $vendor_email         = $vendor_data->getEmail();
                    $vendor_phone         = $vendor_data->getPhone();
                    $vendor_address       = $vendor_data->getAddress();
                    $vendor_city          = $vendor_data->getCity();

                    $bill_to = $vendor_name . "<br>" . $vendor_address . "<br>" . $vendor_contactperson . "<br>Phome: " . $vendor_phone . "<br>Email: " . $vendor_email;

                    $ordercomment = $data['comment'];

                    $poreference_no = $data['poreference_no'];

                    $order_incrementid = $data['orderreference_no'];

                    $order = $this->order->loadByIncrementId($order_incrementid);
                    
                    $addressConfig = $objectManager->get('Magento\Customer\Model\Address\Config');
                    $shipingAddress     = $order->getShippingAddress();
                    $renderer           = $addressConfig->getFormatByCode('html')->getRenderer();
                    $shippingAddressHtml = strip_tags($renderer->renderArray($shipingAddress));
                    
                    $podate           = $objDate->gmtDate();
                    $itemtable = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
                              <thead class="thead-dark" style="background-color:#D80000;border-bottom-color:#D80000;color:white;">
                                <tr>
                                  <th scope="col" colspan="3" style="font-weight: 500;font-size: 12px;padding:3px 9px;">ITEM</th>
                                  <th scope="col" style="text-align:center;font-weight: 500;font-size: 12px;padding:3px 9px;">PRICE</th>
                                  <th scope="col" style="text-align:center;font-weight: 500;font-size: 12px;padding:3px 9px;">QTY</th>
                                  <th scope="col" style="text-align:center;font-weight: 500;font-size: 12px;padding:3px 9px;">TOTAL</th>
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

                    $objectManager     = \Magento\Framework\App\ObjectManager::getInstance();
                    $_transportBuilder = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder');
                    $inlineTranslation = $objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
                    $storeManager      = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
                    $storeManager->setCurrentStore($order->getStore()->getId());
                    $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());

                    $templateVars = array(
                        'poreference_no'    => $poreference_no,
                        'bill_to'           => $bill_to,
                        'ordercomment'      => $ordercomment,
                        'order_incrementid' => $order_incrementid,
                        'ship_to'           => $shippingAddressHtml,
                        'itemtable'         => $itemtable,
                        'podate'            => date('d/m/Y', $currentime),

                    );
                    $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
                    $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);

                    $copy_to = $this->scopeConfig->getValue('sales_email/order/copy_to', ScopeInterface::SCOPE_STORE);

                    $from = array('email' => $email, 'name' => $name);
                    $inlineTranslation->suspend();
                    //$to = array($installer_detail->getEmail());
                    $emailTemplateId = $this->scopeConfig->getValue('purchaseorder/general/po_email_template_id', ScopeInterface::SCOPE_STORE);
                    if ($emailTemplateId != '') {
                            $transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($templateVars)
                            ->setFrom($from)
                            ->addTo($vendor_email)
                            ->addCc($copy_to)
                            ->getTransport();
                        //$transport->sendMessage();
                        $inlineTranslation->resume();
                    }else {
                        $this->messageManager->addError(__('Purchase Order Email Template not configured yet!.'));
                    }
                    $adminUser = $this->authSession->getUser();
                    $orderPoComment = 'PO #'.$last_po_id.' is generated for '.$vendor_name.' Total Qty : '.$totalPoQty . ' - BY ' . $adminUser->getFirstname(). ' '.$adminUser->getLastname();
                    $order->addStatusHistoryComment($orderPoComment);
                    $order->save();
                }

                /*email done */
                $this->messageManager->addSuccess(__('Your purchase order has been created succesfully'));
                //return $resultRedirect->setPath('*/*/grid');
                $params = array('po_id' => $last_po_id);
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
        ///  $resultPage = $this->resultPageFactory->create();
        // $resultPage->setActiveMenu('Hdweb_Installerservice::areamaster');
        // $resultPage->addBreadcrumb(__('Hdweb'), __('Hdweb'));
        // $resultPage->getConfig()->getTitle()->prepend(__('Area Master'));

        //  return $resultPage;
    }

}
