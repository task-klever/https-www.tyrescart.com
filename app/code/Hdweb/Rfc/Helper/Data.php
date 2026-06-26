<?php

namespace Hdweb\Rfc\Helper;

use DateTime;
use DateTimeZone;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $messageManager;
    protected $_objectManager;
    protected $_resource;
    protected $_urlBuilder;
    protected $storeManager;
	protected $authSession;
	protected $date;
	protected $rfc;
	protected $rfcFactory;
	protected $_scopeConfig;

	
    const RNR_LOGIN_URL = 'http://157.175.109.168/ABInternational_FZC/Integration/Api/Login/Login';
	//const ODOO_API_URL = 'https://uncannycs-easyclicktyres-staging-4940313.dev.odoo.com/';	
	const ODOO_API_URL = 'https://uncannycs-easyclicktyres.odoo.com/';	
	const RNR_NOTIFY_EMAIL_TEMPLATE  = 'hdwebcore/general/rnr_notify_email_template';
	const VOID_ORDER_NOTIFY_EMAIL_TEMPLATE  = 'hdwebcore/general/void_order_notify_email_template';
	const INSTALLATION_COMPLETE_NOTIFY_EMAIL_TEMPLATE  = 'hdwebcore/general/installation_complete_notify_email_template';
	const ADMIN_INVOICE_NOTIFY_EMAIL_TEMPLATE  = 'hdwebcore/general/admin_invoice_notify_email_template';
	const SOCIAL_LOGIN_REGISTER_NOTIFY_EMAIL_TEMPLATE  = 'hdwebcore/general/social_login_register_notify_email_template';
	const GOOGLE_REVIEW_NOTIFY_EMAIL_TEMPLATE  = 'hdwebcore/general/google_review_notify_email_template';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Backend\Model\Auth\Session $authSession,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
		\Hdweb\Rfc\Model\Rfc $rfc,
		\Hdweb\Rfc\Model\RfcFactory $rfcFactory
    ) {
        $this->_urlBuilder           = $context->getUrlBuilder();
        $this->storeManager          = $storeManager;
        $this->_objectManager        = \Magento\Framework\App\ObjectManager::getInstance();
        $this->messageManager        = $messageManager;
        $this->_resource             = $resource;
        $this->_scopeConfig          = $context->getScopeConfig();
		$this->authSession 			 = $authSession;
		$this->date        			 = $date;
		$this->rfc        			 = $rfc;
		$this->rfcFactory 			 = $rfcFactory;
    }

    public function getRnrLoginSessionKey($companyId, $apiUsername, $apiPassword)
    {
        $hosturl    = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('rnrtabsection/general/hosturl');
        $loginUrl   = $hosturl . 'Api/Login/Login';
        $sessionKey = '';
        if ($loginUrl != '') {
            $ch              = curl_init($loginUrl);
            $postdata        = array('LoginName' => $apiUsername, 'Password' => $apiPassword, 'Company' => $companyId, 'Branch' => '');
            $postdata_string = json_encode($postdata);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
            $result   = curl_exec($ch);
            $response = json_decode($result, true);
            curl_close($ch);
            /* $sessionKey = '';
            foreach ($response as $responseData) {
            $sessionKey = $responseData['SessionKey'];
            } */
            $sessionKey = $response;
        }
        return $sessionKey;
    }

    public function getRnrSalesInvoiceData($rnr_order_id, $companyId, $apiUsername, $apiPassword)
    {
        $sessionKey = $this->getRnrLoginSessionKey($companyId, $apiUsername, $apiPassword);
        if ($sessionKey != '') {
            // API URL to send data
            $hosturl = "http://157.175.109.168/ABInternational_FZC/Integration/";
			$rfchosturl    = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('rnrtabsection/general/hosturl');
			if($rfchosturl != ''){
				$hosturl = $rfchosturl;
			}
            $rfcUrl  = $hosturl . 'Api/TransactionInt/GetSaleInvoiceData?CompanyCode=' . $companyId . '&OrderNo=' . $rnr_order_id . '&IsPrint=false';

            $rfc_name     = "Get RNR Sales Invoice";
            $rfc_url      = $rfcUrl;
            $requestparam = 'CompanyCode=' . $companyId . '&OrderNo=' . $rnr_order_id . '&IsPrint=false';
            $rfcid        = $this->creaetrfc($rfc_name, $rfc_url, $requestparam);

            $ch      = curl_init();
            $headers = array('Content-Type: application/json', 'SessionKey:' . $sessionKey . '');
            $apiUrl  = str_replace(" ", '%20', $rfcUrl);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Execute curl and assign returned data
            $result = curl_exec($ch);

            $this->updaterfc($rfcid, $result);

            $response = json_decode($result, true);
            // Close curl
            curl_close($ch);
            $invoiceResponse = serialize($response);
            return $invoiceResponse;
        }
    }

    public function getRnrCreateSaleOrder($orderRequest, $companyId, $apiUsername, $apiPassword)
    {
        $sessionKey = $this->getRnrLoginSessionKey($companyId, $apiUsername, $apiPassword);
        if ($sessionKey != '') {
            // API URL to send data
            $hosturl = "http://157.175.109.168/ABInternational_FZC/Integration/";
			$rfchosturl    = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('rnrtabsection/general/hosturl');
			if($rfchosturl != ''){
				$hosturl = $rfchosturl;
			}
            $rfcUrl  = $hosturl . 'Api/TransactionInt/CreateSaleOrder';
            // curl initiate
            $ch = curl_init($rfcUrl);

            $rfc_name     = "Generate RNR ERP Order";
            $rfc_url      = $rfcUrl;
            $requestparam = $orderRequest;
            $rfcid        = $this->creaetrfc($rfc_name, $rfc_url, $requestparam);

            //$postdata        = array('LoginName' => $apiUsername, 'Password' => $apiPassword, 'Company' => $companyId, 'Branch' => '');
            //$postdata_string = json_encode($postdata);
            $headers = array('Content-Type: application/json', 'SessionKey:' . $sessionKey . '');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $orderRequest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            $this->updaterfc($rfcid, $result);

            $response = json_decode($result, true);
            curl_close($ch);
            $orderCreateResponse = serialize($response);
            return $orderCreateResponse;
        }
	}	
	public function getRnrCreatePurchaseOrder($orderRequest, $companyId, $apiUsername, $apiPassword)
    {
        $sessionKey = $this->getRnrLoginSessionKey($companyId, $apiUsername, $apiPassword);
		
        if ($sessionKey != '') {
            // API URL to send data
            $hosturl = "http://157.175.109.168/ABInternational_FZC/Integration/";
			$rfchosturl    = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('rnrtabsection/general/hosturl');
			if($rfchosturl != ''){
				$hosturl = $rfchosturl;
			}
            $rfcUrl  = $hosturl . 'Api/TransactionInt/CreatePurchaseOrder';
            // curl initiate
            $ch = curl_init($rfcUrl);

            $rfc_name     = "Generate RNR ERP Purchase Order";
            $rfc_url      = $rfcUrl;
            $requestparam = $orderRequest;
            $rfcid        = $this->creaetrfc($rfc_name, $rfc_url, $requestparam);

            //$postdata        = array('LoginName' => $apiUsername, 'Password' => $apiPassword, 'Company' => $companyId, 'Branch' => '');
            //$postdata_string = json_encode($postdata);
            $headers = array('Content-Type: application/json', 'SessionKey:' . $sessionKey . '');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $orderRequest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            $this->updaterfc($rfcid, $result);

            $response = json_decode($result, true);
            curl_close($ch);
            $orderCreateResponse = serialize($response);
            return $orderCreateResponse;
        }
    }
	
	public function sendRnrEmailNotification($subject, $rnrOrderResponse)
	{
		//$subject = 'RNR Success Email Notification';
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$_transportBuilder = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
		$email = $this->_scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $name  = $this->_scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
		$from = array('email' => $email, 'name' => $name);
		$to = 'devendra.it@live.com';
		$emailTo    = $this->_scopeConfig->getValue('rnrtabsection/general/rnr_email_receipant', $storeScope);
		if($emailTo != ''){
			$to    = $this->_scopeConfig->getValue('rnrtabsection/general/rnr_email_receipant', $storeScope);
		}
		$emailTemplateId  = $this->_scopeConfig->getValue(self::RNR_NOTIFY_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
		$templateVars = array('subject' => $subject, 'rnr_success_response' => '<pre>'.print_r($rnrOrderResponse, true).'</pre>');
		$transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($from)
			->addTo($to) // $vendor_email
			->getTransport();
		$transport->sendMessage();
		$inlineTranslation->resume();
	}
	
	public function sendVoidEmailNotification($orderIncrementId, $customerEmail, $order_amount)
	{
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$_transportBuilder = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
		$email = $this->_scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $name  = $this->_scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
		$from = array('email' => $email, 'name' => $name);
		$to = $customerEmail;
		$emailTemplateId  = $this->_scopeConfig->getValue(self::VOID_ORDER_NOTIFY_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
		$templateVars = array('order_id' => $orderIncrementId, 'order_amount' => $order_amount);
		$transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($from)
			->addTo($to) // $vendor_email
			->getTransport();
		$transport->sendMessage();
		$inlineTranslation->resume();
	}
	
	public function sendInstallationEmailNotification($orderIncrementId, $customerEmail)
	{
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$_transportBuilder = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
		$email = $this->_scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $name  = $this->_scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
		$from = array('email' => $email, 'name' => $name);
		//$to = $email;
		$to = $customerEmail;
		$emailTemplateId  = $this->_scopeConfig->getValue(self::INSTALLATION_COMPLETE_NOTIFY_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
		$templateVars = array('order_id' => $orderIncrementId);
		$transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($from)
			->addTo($to) // $vendor_email
			->getTransport();
		$transport->sendMessage();
		$inlineTranslation->resume();
	}	
	
	public function updateSTGproduct($productRequest)
    {
		$authUser = 'test';
		$authPassword = '!test123!';
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://www.stopandgouae.com/rest/V1/products/updateProduct?user_name=tyoapi&password=Tyoapiint@2022',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $productRequest,
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		  ),
		 // CURLOPT_USERPWD => $authUser.":".$authPassword,
		  CURLOPT_HTTPAUTH => CURLAUTH_ANY
		));

		$response = curl_exec($curl);

		curl_close($curl);
        
	}
	public function updateStaffproduct($productRequest)
    {
		$authUser = 'test';
		$authPassword = '!test123!';
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://staff.stopandgouae.com/rest/V1/products/updateProduct?user_name=tyoapi&password=Tyoapiint@2022',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $productRequest,
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		  ),
		 // CURLOPT_USERPWD => $authUser.":".$authPassword,
		  CURLOPT_HTTPAUTH => CURLAUTH_ANY
		));

		$response = curl_exec($curl);
		
		curl_close($curl);
        
	}
	
	public function sendAdminInvoiceEmailNotification($order)
	{
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$_transportBuilder = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
		$email = $this->_scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $name  = $this->_scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
		$from = array('email' => $email, 'name' => $name);
		$receiveemails    = $this->_scopeConfig->getValue('hdwebinvoice/general/custom_invoice_email_receipant', $storeScope);
		
		
		$receiveemailsTo = explode(',',preg_replace('/\s+/', '', $receiveemails));

		// $receiveemailsTo = explode(',', preg_replace('/\s+/', '', $receiveemails ?? ''));
		

		$emailTemplateId  = $this->_scopeConfig->getValue(self::ADMIN_INVOICE_NOTIFY_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
		$billingAddress  = $order->getBillingAddress();
		$order_amount     = $order->getGrandTotal();
		$order_amount = number_format($order_amount, 2, '.', '');
		$transaction = $this->_objectManager->create('Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory')->create()->addOrderIdFilter($order->getId())->getFirstItem();
		$transactionId = '';
		if(count($transaction->getData()) > 0){
			$transactionId = $transaction->getData('txn_id');
		}
		$payment            = $order->getPayment();
		$method             = $payment->getMethodInstance();
		$paymentmethodTitle = $method->getTitle();
		$templateVars = array(
							'order_id'		 	=> $order->getIncrementId(),
							'customer_name' 	=> $order->getCustomerName(),
							'customer_email' 	=> $order->getCustomerEmail(),
							'customer_contact' 	=> $billingAddress->getTelephone(),
						    'payment_id' 	  	=> $transactionId,
						    'payment_mode'    	=> $paymentmethodTitle,
							'grand_total' 		=> $order_amount,
		);
		$transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($from)
			->addTo($receiveemailsTo)
			->getTransport();
		$transport->sendMessage();
		$inlineTranslation->resume();
	}
	
	public function sendLoginDiscountEmailNotification($customerEmail, $couponCode)
	{
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$_transportBuilder = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
		$email = $this->_scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $name  = $this->_scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
		$from = array('email' => $email, 'name' => $name);
		$to = $customerEmail;
		$emailTemplateId  = $this->_scopeConfig->getValue(self::SOCIAL_LOGIN_REGISTER_NOTIFY_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
		$templateVars = array('coupon_code' => $couponCode);
		$transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($from)
			->addTo($to) // $vendor_email
			->getTransport();
		$transport->sendMessage();
		$inlineTranslation->resume();
	}
	
	public function sendGoogleReviewEmailNotification($customerEmail, $customerName)
	{
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$_transportBuilder = $this->_objectManager->create('Hdweb\Purchaseorder\Model\Mail\TransportBuilder');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
		$email = $this->_scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $name  = $this->_scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
		$from = array('email' => $email, 'name' => $name);
		$to = $customerEmail;
		$emailTemplateId  = $this->_scopeConfig->getValue(self::GOOGLE_REVIEW_NOTIFY_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
		$templateVars = array('customer_name' => $customerName);
		$transport = $_transportBuilder->setTemplateIdentifier($emailTemplateId)
			->setTemplateOptions($templateOptions)
			->setTemplateVars($templateVars)
			->setFrom($from)
			->addTo($to)
			->getTransport();
		$transport->sendMessage();
		$inlineTranslation->resume();
	}
	
	public function creaetrfc($rfc_name, $rfc_url, $requestparam)
    {
        $adminuser    = $this->authSession->getUser();
        $rfc_username = '';
		if($adminuser){
			$rfc_username = $adminuser->getFirstname() . ' ' . $adminuser->getLastname();
		}
        $rfc_datetime = $this->date->date()->format('Y-m-d H:i:s');

        $rfc = $this->rfc;
        $rfc->setData('rfc_name', $rfc_name);
        $rfc->setData('rfc_url', $rfc_url);
        $rfc->setData('rfc_username', $rfc_username);
        $rfc->setData('rfc_datetime', $rfc_datetime);
        $rfc->setData('requestparam', $requestparam);
        $rfc->save();
        $rfcid = $rfc->getRfcId();
        return $rfcid;
    }

    public function updaterfc($rfcid, $responseparam)
    {
        $rfc_response_datetime = $this->date->date()->format('Y-m-d H:i:s');
        $rfc                   = $this->rfc->load($rfcid);
        $rfc->setData('rfc_response_datetime', $rfc_response_datetime);
        $rfc->setData('responseparam', $responseparam);
        $rfc->save();
    }
	
	public function createOdooSalesOrder($orderRequest, $tokenKey)
    {
        if ($tokenKey != '') {
            // API URL to send data
            $apiUrl = self::ODOO_API_URL."sale/create/so";
            // curl initiate
            $ch = curl_init($apiUrl);
            $rfc_name     = "Generate Odoo Sales Order";
            $requestparam = $orderRequest;
            $rfcid        = $this->creaetrfc($rfc_name, $apiUrl, $requestparam);
            $headers = array('Content-Type: application/json');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $orderRequest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            $this->updaterfc($rfcid, $result);

            //$response = json_decode($result, true);
            curl_close($ch);
            $orderCreateResponse = serialize($result);
            return $orderCreateResponse;
        }
	}
	
	public function createOdooPurchaseOrder($orderRequest, $tokenKey)
    {
        if ($tokenKey != '') {
            // API URL to send data
            $apiUrl = self::ODOO_API_URL."purchase/create/po";
            // curl initiate
            $ch = curl_init($apiUrl);
            $rfc_name     = "Generate Odoo ERP Purchase Order";
            $requestparam = $orderRequest;
            $rfcid        = $this->creaetrfc($rfc_name, $apiUrl, $requestparam);
            $headers = array('Content-Type: application/json');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $orderRequest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            $this->updaterfc($rfcid, $result);
            //$response = json_decode($result, true);
            curl_close($ch);
            $orderCreateResponse = serialize($result);
            return $orderCreateResponse;
        }
    }

    public function sendWhatsAppNotification($order, $templateId, $notifyInstaller = null, $notifyCustomer = null, $orderUpdateComment = null, $po = true, $postData = null){
		
		$storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$whatsAppEnable	= $this->_scopeConfig->getValue('hdwebapi/general/whatsapp_notify', $storeScope);
		$whatsAppTokenKey = $this->_scopeConfig->getValue('hdwebapi/general/whatsapp_tokenkey', $storeScope);
		if($whatsAppEnable && $whatsAppTokenKey){
			
			$collection = $this->rfcFactory->create()->getCollection()
                    ->addFieldToFilter('order_id', $order->getIncrementId())
                    ->getLastItem();
			$trengoCustomerTicketId = $collection->getTrengoCustomerTicketId();
						
			$billingAddress  = $order->getBillingAddress();
			$curl = curl_init();
			$apiUrl = 'https://app.trengo.com/api/v2/wa_sessions';
			$tokenKey = $whatsAppTokenKey; //'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiODdlZWM0ZDBjY2ZiYjUwYmVmN2U1NWVhZjZmZDg2ZjE3ZmFjODQ5MGM5MmFmMDZmNWYyYTNlODNlZjQ0MzMyN2RlMjRjM2JhNDhiZDNiYjciLCJpYXQiOjE2Njc5Nzg2MjEuOTE4NzMzLCJuYmYiOjE2Njc5Nzg2MjEuOTE4NzM1LCJleHAiOjQ3OTIxMTYyMjEuOTA3NjA5LCJzdWIiOiI1MDk1NjQiLCJzY29wZXMiOltdfQ.A7KkmrZrwj5bxqq4rM3D5YLWUx-a0zA4Py1EKYyJvLLZBkEnFMrc_rimee8Dc2qfNuiJBhZ2Sl2RTbtiHGycaQ';
			//$templateId = 56254;
			
			//$phoneNumber = '+971'.$billingAddress->getTelephone();
			$phoneNumber = '+919427621985';
			$notifyName = $order->getCustomerName();
			
			$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"}],"hsm_id":"'.$templateId.'","recipient_phone_number":"'.$phoneNumber.'"}';
			
			if(!empty($trengoCustomerTicketId)){
				$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"}],"hsm_id":"'.$templateId.'","ticket_id":"'.$trengoCustomerTicketId.'"}';
			}
			
			$rfc_name     = "Send WhatsApp Notification Order #".$order->getIncrementId();
			
			if($notifyInstaller){
				if($postData['phone']){
					$phoneNumber = '+971'.$postData['phone'];
					$notifyName = $postData['installer_name'];
					$date = $postData['date_time'];
					$comment = $postData['comment'];
				}
				$rfc_name     = "Notify Installer WhatsApp Notification Order #".$order->getIncrementId();
				$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"},{"key":"{{3}}","value":"'.$date.'"},{"key":"{{4}}","value":"'.$comment.'"}],"hsm_id":"'.$templateId.'","recipient_phone_number":"'.$phoneNumber.'"}';
			}
			if($notifyCustomer){
				$rfc_name     = "Notify Customer WhatsApp Notification Order #".$order->getIncrementId();
				$date = $postData['date_time'];
				$comment = $postData['comment'];
				$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"},{"key":"{{3}}","value":"'.$date.'"},{"key":"{{4}}","value":"'.$comment.'"}],"hsm_id":"'.$templateId.'","recipient_phone_number":"'.$phoneNumber.'"}';
				if(!empty($trengoCustomerTicketId)){
					$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"},{"key":"{{3}}","value":"'.$date.'"},{"key":"{{4}}","value":"'.$comment.'"}],"hsm_id":"'.$templateId.'","ticket_id":"'.$trengoCustomerTicketId.'"}';
				}
			}
			if($po){
				$phoneNumber = '+971'.$postData['vendor_phone'];
				$poreference_no = $postData['poreference_no'];
				$comment = $postData['comment'];
				$pdfLink = $postData['pdf_link'];
				$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$poreference_no.'"},{"key":"{{2}}","value":"'.$comment.'"},{"key":"{{3}}","value":"'.$pdfLink.'"}],"hsm_id":"'.$templateId.'","recipient_phone_number":"'.$phoneNumber.'"}';
				$rfc_name     = "Purchase Order WhatsApp Notification PO #".$poreference_no;
			}
			if($orderUpdateComment){
				$rfc_name     = "Order Update WhatsApp Notification Order #".$order->getIncrementId();
				$comment = $postData['comment'];
				$orderStatus = $postData['order_status'];
				$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"},{"key":"{{3}}","value":"'.$orderStatus.'"},{"key":"{{4}}","value":"'.$comment.'"}],"hsm_id":"'.$templateId.'","recipient_phone_number":"'.$phoneNumber.'"}';
				if(!empty($trengoCustomerTicketId)){
					$orderRequest = '{"params":[{"key":"{{1}}","value":"'.$notifyName.'"},{"key":"{{2}}","value":"'.$order->getIncrementId().'"},{"key":"{{3}}","value":"'.$orderStatus.'"},{"key":"{{4}}","value":"'.$comment.'"}],"hsm_id":"'.$templateId.'","ticket_id":"'.$trengoCustomerTicketId.'"}';
				}
			}
					
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $apiUrl,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS => $orderRequest,
			  CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.$tokenKey.'',
				'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);
			
			curl_close($curl);
			
			$responsFinal = json_decode($response);
			//if(isset($responsFinal->message->ticket_id))
			
			$totalCount = 1;
			$successcount = 0;
			$failedcount = 1;
			$status = 'Failed';
			$trengo_customer_ticket_id = '';
			if(isset($responsFinal->message->ticket_id)){
				$status = 'Success';
				$successcount = 1;
				$failedcount = 0;
				$trengo_customer_ticket_id = $responsFinal->message->ticket_id;
			}
			
			$requestparam = $orderRequest;
			$rfcid        = $this->creaetrfc($rfc_name, $apiUrl, $requestparam);
			$rfc_response_datetime = $this->date->date()->format('Y-m-d H:i:s');
			$rfc                   = $this->rfc->load($rfcid);
			$rfc->setData('rfc_response_datetime', $rfc_response_datetime);
			$rfc->setData('responseparam', $response);
			$rfc->setData('rfc_status', $status);
			$rfc->setData('rfc_total_record', $totalCount);
			$rfc->setData('rfc_total_sucess', $successcount);
			$rfc->setData('rfc_total_fail', $failedcount);
			$rfc->setData('order_id', $order->getIncrementId());
			$rfc->setData('trengo_customer_ticket_id', $trengo_customer_ticket_id);
			$rfc->save();
			
			return $responsFinal;	
		}
	}

	public function generateCoupon($order)
	{
		$productCategory = $this->_objectManager->get('Magento\Catalog\Model\ProductCategoryList');
		$createCoupon = 0;
		foreach ($order->getAllVisibleItems() as $_item) {
			$product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
			$parts_category = $product->getResource()->getAttribute("parts_category")->getFrontend()->getValue($product);
			if($parts_category == 'Tyres' || $parts_category == 'Wheel Protector'){
				$createCoupon = 1;
				break;
			}
		}

		if($createCoupon == 1){
			$coupon['name'] = 'OSA - ' . uniqid();
		    $coupon['desc'] = 'OSA';
		    $coupon['start'] = date('Y-m-d');
		    $coupon['end'] = date('Y-m-d', strtotime('+1 year'));;
		    $coupon['max_redemptions'] = 1;
		    $coupon['discount_type'] = 'by_percent';
		    $coupon['discount_amount'] = 15;
		    //$coupon['product_ids'] = $productId;
		    //$coupon['minimum_amount'] = $couponData['general']['minimum_amount'];
		    $coupon['flag_is_free_shipping'] = 'no';
		    $coupon['redemptions'] = 1;
		    $coupon['code'] = $order->getIncrementId();

		    $shoppingCartPriceRule = $this->_objectManager->create('Magento\SalesRule\Model\Rule');

		    $shoppingCartPriceRule->setName($coupon['name'])
		            ->setDescription($coupon['desc'])
		            ->setFromDate($coupon['start'])
		            ->setToDate($coupon['end'])
		            ->setUsesPerCustomer($coupon['max_redemptions'])
		            ->setCustomerGroupIds(array('0', '1', '2', '3',))
		            ->setIsActive(1)
		            ->setSimpleAction($coupon['discount_type'])
		            ->setDiscountAmount($coupon['discount_amount'])
		            ->setDiscountQty(1)
		            ->setApplyToShipping($coupon['flag_is_free_shipping'])
		            ->setTimesUsed($coupon['redemptions'])
		            ->setWebsiteIds(array('1'))
		            //->setProductIds($coupon['product_ids'])
		            ->setCouponType(2)
		            ->setCouponCode($coupon['code'])
		            ->setUsesPerCoupon(1)
		            ->setStopRulesProcessing(0);

		    $conditions = array();
		    $conditions["1"] = array
		        (
		        "type" => "Magento\SalesRule\Model\Rule\Condition\Combine",
		        "aggregator" => "all",
		        "attribute" => null,
		        "operator" => null,
		        "value" => 1,
		        "is_value_processed" => null,
		    );
		    $conditions["1--1"] = array
		        (
		        "type" => "Magento\SalesRule\Model\Rule\Condition\Product\Found",
		        "attribute" => null,
		        "operator" => null,
		        "value" => 1,
		        "is_value_processed" => null,
		        "aggregator" => "all",
		    );
		    $conditions["1--1--1"] = array
		        (
		        "type" => "Magento\SalesRule\Model\Rule\Condition\Product",
		        "attribute" => "category_ids",
		        "operator" => "()",
		        "value" => [7]
		    );
		    /*$conditions["1--1--1-1"] = array
		        (
		        "type" => "Magento\SalesRule\Model\Rule\Condition\Product",
		        "attribute" => "quote_item_qty",
		        "operator" => ">=",
		        "value" => $responsData->quantity
		    );*/
		    $shoppingCartPriceRule->setData('conditions', $conditions);
			$shoppingCartPriceRule->setData('actions', $conditions);
		    // Validating rule data before Saving
		    $validateResult = $shoppingCartPriceRule->validateData(new \Magento\Framework\DataObject($shoppingCartPriceRule->getData()));
		    if ($validateResult !== true) {
		        foreach ($validateResult as $errorMessage) {
		            echo $errorMessage;
		        }
		        return;
		    }

		    try {
		        $shoppingCartPriceRule->loadPost($shoppingCartPriceRule->getData());
		        $shoppingCartPriceRule->save();

		        $ruleJob = $this->_objectManager->get('Magento\CatalogRule\Model\Rule\Job');
		        $ruleJob->applyAll();
		    } catch (Exception $e) {
		        echo $e->getMessage();
		    }
		}
		return;

	}

	public function getBicyclePatternProducts($patternId,$productId)
	{
		$productFactory = $this->_objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$collection = $productFactory->create();
		$collection->addAttributeToSelect('entity_id');
		$collection->addAttributeToSelect('url_key');
		$collection->addAttributeToSelect('wheel_size');
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$collection->addAttributeToFilter('pattern', $patternId);
		$collection->addAttributeToFilter('entity_id', array('neq' => $productId));
		$collection->addAttributeToFilter('wheel_size', array('neq' => ''));
		$collection->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id', 'is_in_stock=1');

		return $collection;
	}

	public function getOptionLabelByValue($attributeCode,$optionId)
    {
        $productFactory = $this->_objectManager->get('Magento\Catalog\Model\ProductFactory');
        $product = $productFactory->create();
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode); 
        $optionText = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionText = $isAttributeExist->getSource()->getOptionText($optionId);
        }
        return $optionText;
    }
	
	public function pushOrderToGoogleTag($order)
    {
        $data             = $this->getCheckoutSuccessDataEachOrderCustom($order);
        $data['enhanced'] = $this->getEnhancedConversionDataCustom($order);
		//echo '<pre>';print_r($data);die;
        return $data;
    }
	
	public function pushOrderToGoogleAnalytics($order)
    {
		$gtmHelper = $this->_objectManager->create('Mageplaza\GoogleTagManager\Helper\Data');
        $products = [];
        $items    = $order->getItemsCollection([], true);
        foreach ($items as $item) {
            $products[] = $gtmHelper->getCheckoutProductData($item);
        }

        $data = [
            'event_name' => ['purchase'],
            'data'       => [
                'transaction_id' => $order->getIncrementId(),
                'value'          => $gtmHelper->calculateTotals($order),
                'currency'       => $gtmHelper->getCurrentCurrency(),
                'tax'            => $order->getTaxAmount(),
                'shipping'       => $order->getShippingAmount(),
                'items'          => $products
            ]
        ];
		//echo '<pre>';print_r($data);die;
        return $data;
    }
	
	/**
     * @param Order $order
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCheckoutSuccessDataEachOrderCustom($order)
    {
		$gtmHelper = $this->_objectManager->create('Mageplaza\GoogleTagManager\Helper\Data');
		$timezone = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$localeResolver = $this->_objectManager->get('Magento\Framework\Locale\Resolver');
        $items = $order->getItemsCollection([], true);

        $products    = [];
        $productsGa4 = [];
        $skuItems    = [];
        $skuItemsQty = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $productSku    = $item->getSku();
            $products[]    = $gtmHelper->getProductOrderedData($item);
            $skuItems[]    = $productSku;
            $skuItemsQty[] = $productSku . ':' . (int) $item->getQtyOrdered();
            if ($gtmHelper->isEnabledGTMGa4()) {
                $productsGa4[] = $gtmHelper->getGa4ProductOrderedData($item);
            }
        }

        $itemsData = [];
        foreach ($products as $product) {
            $itemsData[] = [
                'id'                       => $product['id'],
                'google_business_vertical' => 'retail'
            ];
        }

        $data['remarketing_event'] = 'purchase';
        $data['value']             = $gtmHelper->calculateTotals($order);
        $data['items']             = $itemsData;

        $createdAt = $timezone->date(
            new DateTime($order->getCreatedAt(), new DateTimeZone('UTC')),
            $localeResolver->getLocale(),
            true
        );

        $data['ecommerce'] = [
            'purchase'     => [
                'actionField' => [
                    'id'          => $order->getIncrementId(),
                    'affiliation' => $gtmHelper->getAffiliationName(),
                    'order_id'    => $order->getIncrementId(),
                    'subtotal'    => $order->getSubtotal(),
                    'shipping'    => $order->getShippingAmount(),
                    'tax'         => $order->getTaxAmount(),
                    'revenue'     => $gtmHelper->calculateTotals($order),
                    'discount'    => $order->getDiscountAmount(),
                    'coupon'      => (string) $order->getCouponCode(),
                    'created_at'  => $createdAt->format('Y-m-d H:i:s'),
                    'items'       => implode(';', $skuItems),
                    'items_qty'   => implode(';', $skuItemsQty)
                ],
                'products'    => $products
            ],
            'currencyCode' => $gtmHelper->getCurrentCurrency()
        ];

        if ($gtmHelper->isEnabledGTMGa4()) {
            $data['ga4_event']                   = 'purchase';
            $data['ecommerce']['transaction_id'] = $order->getIncrementId();
            $data['ecommerce']['affiliation']    = $gtmHelper->getAffiliationName();
            $data['ecommerce']['value']          = $gtmHelper->calculateTotals($order);
            $data['ecommerce']['tax']            = $order->getTaxAmount();
            $data['ecommerce']['shipping']       = $order->getShippingAmount();
            $data['ecommerce']['currency']       = $gtmHelper->getCurrentCurrency();
            $data['ecommerce']['coupon']         = (string) $order->getCouponCode();
            $data['ecommerce']['items']          = $productsGa4;
        }
		
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/gtm.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('=== START ===');
		$logger->info(print_r($data, true));
		$logger->info('=== END ===');
		
        return $data;
    }
	
	/**
     * Get enhanced conversion tracking data
     *
     * @param $order
     *
     * @return array
     */
    public function getEnhancedConversionDataCustom($order)
    {
        $data = [];
        /** @var Order $order */
        $shippingAddess = $order->getShippingAddress();

        $data['email']       = $shippingAddess->getEmail() ?: '';
        $data['first_name']  = $shippingAddess->getFirstname() ?: '';
        $data['last_name']   = $shippingAddess->getLastname() ?: '';
        $data['phone']       = $shippingAddess->getTelephone() ?: '';
        $data['street']      = implode(', ', $shippingAddess->getStreet()) ?: '';
        $data['city']        = $shippingAddess->getCity() ?: '';
        $data['region']      = $shippingAddess->getRegion() ?: '';
        $data['postal_code'] = $shippingAddess->getPostcode() ?: '';
        $data['country']     = $shippingAddess->getCountryId() ?: '';

        return $data;
    }
}