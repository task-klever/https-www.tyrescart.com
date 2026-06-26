<?php
 
namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
     
    protected $resultPagee;

	protected $resultPageFactory;

    public function __construct(
		Context $context, 
		PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute() {
      
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$data  = $this->_request->getParams();
		$poType = $data['po_type'];
		$orderId = $data['order_id'];
		$orderRepository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');
		$order = $orderRepository->get($orderId);
		$orderreference_no = $order->getIncrementId();
		$orderItems = $order->getAllItems();
		$itemQty =  array();
		foreach ($orderItems as $item) {
		  $itemQty[] = $item->getQtyOrdered();
		}
		$totalOrderedQty = array_sum($itemQty);
		$totalPurchaseOrderQty = $objectManager->create('Hdweb\Purchaseorder\Helper\Data')->validatePurchaseOrder($orderreference_no,$poType);
		if (!in_array($data['po_type'], ['dpo', 'ppo'])) {
			if($totalPurchaseOrderQty >= $totalOrderedQty){
				$this->messageManager->addError(__('Total Purchase Qty is more or equal than Ordered Qty. Please check the all Purchase Order for this Order. Ordered Qty is '.$totalOrderedQty.' and Purchase Order Qty is '.$totalPurchaseOrderQty));
			}
		}
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Purchase Order'));

        return $resultPage;
    }

}