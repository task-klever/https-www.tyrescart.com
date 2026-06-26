<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;
use Magento\Store\Model\ScopeInterface;

class Sendorderemail extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $scopeConfig ;
    protected $addressRenderer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
          \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
        )
    {
            parent::__construct($context);
            $this->_order = $order; 
            $this->scopeConfig  = $scopeConfig;
            $this->addressRenderer = $addressRenderer;
    }
    public function execute()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->_order->load($order_id);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$email_sent = $order->getEmailSent();
		$state      = $order->getState();
		$allstate   = array('pending', 'paytabs_failed', 'canceled');
		//if (!$email_sent && (!in_array($state, $allstate))){
			$emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
			$emailSender->send($order, false, true);
			$history = $order->addStatusHistoryComment(__('From Admin : An order confirmation email is sent to customer.'));
			$history->save();
			$order->save();
			$this->messageManager->addSuccess(__('Email has been sent.'));
		//}
		$this->_redirect('sales/order/view', array('order_id' => $order_id));
    }
}