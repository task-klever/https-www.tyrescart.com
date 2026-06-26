<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;

class Updatedelivery extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $orderStatusRepository;
    protected $authSession;
    protected $storeLocator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Ecomteck\StoreLocator\Model\Stores $storeLocator
    ) {
        parent::__construct($context);
        $this->_order = $order;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->authSession = $authSession;
        $this->storeLocator = $storeLocator;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $installerId = $this->getRequest()->getParam('delivery_installer');
        $pickupLocation = $this->getRequest()->getParam('delivery_pickup_location');
        $pickupDate = $this->getRequest()->getParam('delivery_date');
        $pickupTime = $this->getRequest()->getParam('delivery_time');
        $orderComment = $this->getRequest()->getParam('delivery_comment');

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Order not found.'));
            $this->_redirect('sales/order/index');
            return;
        }

        try {
            $order = $this->_order->load($orderId);

            if ($installerId) {
                $order->setPickupStore($installerId);

                // Update shipping description based on installer type
                $installer = $this->storeLocator->load($installerId);
                if ($installer->getId()) {
                    if ($installer->getIsmobilevan()) {
                        $order->setShippingDescription('Mobile Van Service');
                    } else {
                        $order->setShippingDescription('Install at Outlet');
                    }
                }
            }
            if ($pickupDate !== null) {
                $order->setPickupDate($pickupDate);
            }
            if ($pickupTime !== null) {
                $order->setPickupTime($pickupTime);
            }
            if ($pickupLocation !== null) {
                $order->setPickupLocation($pickupLocation);
            }

            $adminUser = $this->authSession->getUser();

            // Save order comment as status history
            if ($orderComment) {
                $commentObj = $order->addStatusHistoryComment($orderComment);
                $this->orderStatusRepository->save($commentObj);
            }

            // Add system log for delivery update
            $updateLog = $order->addStatusHistoryComment(
                'Update - Delivery Details - Date: ' . $pickupDate . ' - Time: ' . $pickupTime
                . ($pickupLocation ? ' - Location: ' . $pickupLocation : '')
                . ' - BY ' . $adminUser->getFirstname() . ' ' . $adminUser->getLastname()
            );
            $this->orderStatusRepository->save($updateLog);
            $order->save();

            $this->messageManager->addSuccessMessage(__('Delivery details have been updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error updating delivery details: %1', $e->getMessage()));
        }

        $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
