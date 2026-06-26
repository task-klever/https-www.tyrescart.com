<?php
namespace Ecomteck\StorePickup\Controller\Fitment;

class SetFitmentType extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->resultJsonFactory        = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $postData  = $this->getRequest()->getParams();
        $fitmentType = $postData['fitment_type'];
        $quote = $this->checkoutSession->getQuote();
        $quote->setPickupDate('');
        $quote->setPickupTime('');
        $quote->setPickupStore('');
        $quote->setFitmentType($fitmentType);
            
        if ($quote->save()) {
            $responseData = ['status' => 'success', 'message' => 'Fitment Type Is Saved In Quote' , 'fitment_type' => $fitmentType];
        } else {
            $responseData = ['status' => 'error', 'message' => 'Fitment Type Not Saved In Quote'];
        }
        
        $result = $this->resultJsonFactory->create();
        $result->setData($responseData);
        return $result;
    }

}