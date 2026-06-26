<?php

namespace Ecomteck\StoreLocator\Controller\Ajax;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class SetInstaller extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $request;
    protected $_checkoutSession;
    protected $messageManager;
    protected $store;

    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Ecomteck\StoreLocator\Model\StoresFactory $store,
        JsonFactory $resultJsonFactory
    ) {
        $this->request           = $request;
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession  = $_checkoutSession;
        $this->messageManager    = $context->getMessageManager();
        $this->store             = $store;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = [];
        $post_Param = $this->getRequest()->getParams();
        $quote = $this->_checkoutSession->getQuote();

        $pickup_date  = $post_Param['pickup_date'] ?? date("m/d/Y", strtotime("+2 days"));
        $pickup_time  = $post_Param['pickup_time'] ?? '06:00 PM - 09:00 PM';

        if (isset($post_Param['pickup_store'])) {
            $pickup_store = $post_Param['pickup_store'];

            $quote->setPickupDate($pickup_date);
            $quote->setPickupTime($pickup_time);
            $quote->setPickupStore($pickup_store);

            if (isset($post_Param['pickup_location'])) {
                $quote->setPickupLocation($post_Param['pickup_location']);
            }

            $quote->save();

            $response = [
                'status' => 'success',
                'message' => 'Installer Selected'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Installer Not Selected'
            ];
        }

        if(isset($post_Param['pickup_type']) && $post_Param['pickup_type'] == 'mobile_van'){
            $description = 'Mobile Van Service';
        }elseif(isset($post_Param['pickup_type']) && $post_Param['pickup_type'] == 'normal'){
            $description = 'Install at Outlet';
        }else{
            $description = 'Delivery - Without Fitment';
        }

        // --- Add shipping description ---
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            // If the quote has no shipping address yet, create one
            $shippingAddress = $quote->getShippingAddress() ?: $quote->addAddress();
            $shippingAddress->setAddressType('shipping');
        }

        $shippingAddress->setShippingMethod('storepickup_storepickup'); // optional code
        $shippingAddress->setShippingDescription($description);
        $shippingAddress->setCollectShippingRates(false);

        // Apply the selected store's shipping amount (or 0 if none)
        $shippingAmount = 0;
        if (isset($pickup_store) && $pickup_store) {
            $storeModel = $this->store->create()->load($pickup_store);
            if ($storeModel->getId()) {
                $shippingAmount = (float) $storeModel->getShippingAmount();
            }
        }
        $shippingAddress->setShippingAmount($shippingAmount);
        $shippingAddress->setBaseShippingAmount($shippingAmount);

        // optional: if you want to ensure it's also saved on quote
        $shippingAddress->save();

        return $this->resultJsonFactory->create()->setData($response);
    }
}
