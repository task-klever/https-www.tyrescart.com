<?php
/**
 * Notify Invoice – Save/update invoice comment without sending email.
 */
declare(strict_types=1);

namespace Klever\OrderActions\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;

class Update extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusRepository;

    /**
     * @var AuthSession
     */
    private $authSession;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        AuthSession $authSession
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->authSession = $authSession;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $comment = (string) $this->getRequest()->getParam('invoice_comment', '');

        try {
            $order = $this->orderRepository->get($orderId);

            /* Save comment to order status history */
            if ($comment) {
                $adminUser = $this->authSession->getUser();
                $adminName = $adminUser
                    ? $adminUser->getFirstname() . ' ' . $adminUser->getLastname()
                    : 'Admin';
                $historyComment = $order->addStatusHistoryComment(
                    'Notify - Invoice - ' . $comment . ' - BY ' . $adminName
                );
                $this->orderStatusRepository->save($historyComment);
            }

            $this->messageManager->addSuccessMessage(__('Invoice comment has been updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
