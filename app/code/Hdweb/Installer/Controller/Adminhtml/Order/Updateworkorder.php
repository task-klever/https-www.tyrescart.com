<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;

class Updateworkorder extends \Magento\Backend\App\Action
{
    protected $_order;
    protected $orderStatusRepository;
    protected $authSession;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        parent::__construct($context);
        $this->_order = $order;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->authSession = $authSession;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');
        $installerDate = $this->getRequest()->getParam('installer_date');
        $installerComment = $this->getRequest()->getParam('installer_comment');

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Order not found.'));
            $this->_redirect('sales/order/index');
            return;
        }

        try {
            $order = $this->_order->load($orderId);
            $order->setWorkorderDatetime($installerDate);
            $order->setWorkorderComment($installerComment);

            $adminUser = $this->authSession->getUser();
            $comment = $order->addStatusHistoryComment(
                'Update - Workorder - ' . $installerDate . ' - ' . $installerComment . ' - BY ' . $adminUser->getFirstname() . ' ' . $adminUser->getLastname()
            );
            $this->orderStatusRepository->save($comment);
            $order->save();

            $this->messageManager->addSuccessMessage(__('Work order details have been updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error updating work order: %1', $e->getMessage()));
        }

        $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
