<?php
namespace Hdweb\HyvaCheckoutVehicleInfo\Controller\Ajax;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SelectedYear extends \Magento\Framework\App\Action\Action
{	
	protected $resultJsonFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $checkoutSession;
	
	
    public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
	) {
		$this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
    	parent::__construct($context);
    }
    
    public function execute()
    {   
		$year = $this->getRequest()->getParam('year');
        $plate = $this->getRequest()->getParam('plate');
		$quote = $this->checkoutSession->getQuote();
        $quote->setYear($year);
        $quote->setPlate($plate);
        if($quote->save()){
            $responseArray = ['status' => 'success', 'message' => 'Year and Plate saved successfully'];
        }
    	
		$response['response'] = $responseArray;
		$resultJson = $this->resultJsonFactory->create();
		return $resultJson->setData($response);
    }	
}