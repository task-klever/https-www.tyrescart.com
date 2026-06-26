<?php
namespace Ecomteck\StoreLocator\Controller\Index;

class SelectStore extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $postData  = $this->getRequest()->getParams();
        if (isset($postData['installer_id']) && isset($postData['pickup_date']) && isset($postData['pickup_time'])) {
            $storeId = $postData['installer_id'];
            $pickup_date = $postData['pickup_date'];
            $pickupTime = $postData['pickup_time'];
            $date = str_replace('/', '-', $pickup_date);
            $pickupDate = date('Y-m-d', strtotime($date));
            $quote = $this->checkoutSession->getQuote();
            $quote->setPickupDate($pickupDate);
            $quote->setPickupTime($pickupTime);
            $quote->setPickupStore($storeId);
            $quote->save();

            $this->checkoutSession->setPickupdate($pickupDate);
            $this->checkoutSession->setPickuptime($pickupTime);
            $this->checkoutSession->setPickupstoreid($storeId);

            $this->_redirect('checkout');
        }else{
            $this->_redirect('storepickup');
        }
    }

}