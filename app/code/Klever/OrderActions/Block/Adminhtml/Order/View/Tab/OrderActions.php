<?php

declare(strict_types=1);

namespace Klever\OrderActions\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class OrderActions extends Template implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Klever_OrderActions::order/view/tab/order_actions.phtml';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var BackendUrl
     */
    private $backendUrl;

    public function __construct(
        Context $context,
        Registry $registry,
        BackendUrl $backendUrl,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    /**
     * Get current order.
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get current order ID.
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return (int) $this->getOrder()->getEntityId();
    }

    /**
     * Generate purchase order URL.
     *
     * @param string $poType
     * @param array $extraParams
     * @return string
     */
    public function getPurchaseOrderUrl(string $poType, array $extraParams = []): string
    {
        $params = array_merge(
            ['order_id' => $this->getOrderId(), 'po_type' => $poType],
            $extraParams
        );
        return $this->backendUrl->getUrl('purchaseorder/create/index', $params);
    }

    /**
     * Check if abandoned mail button should be shown.
     *
     * @return bool
     */
    public function canShowAbandonedMail(): bool
    {
        $orderStatus = strtolower((string) $this->getOrder()->getStatus());
        $allowedStatuses = ['expired', 'cancel', 'pending', 'pending_payment'];
        return in_array($orderStatus, $allowedStatuses, true);
    }

    /**
     * Check if abandoned mail is restricted (sent within last 24h).
     *
     * @return bool
     */
    public function isAbandonedMailRestricted(): bool
    {
        $order = $this->getOrder();
        $isAbandonedSend = (int) $order->getData('is_abandoned_send');
        $abandonedSendAt = $order->getData('abandoned_send_at');

        if ($isAbandonedSend && $abandonedSendAt) {
            $sendTime = strtotime($abandonedSendAt);
            $currentTime = time();
            $hoursPassed = ($currentTime - $sendTime) / 3600;
            return $hoursPassed < 24;
        }

        return false;
    }

    /**
     * Get abandoned mail send URL.
     *
     * @return string
     */
    public function getAbandonedMailSendUrl(): string
    {
        return $this->getUrl('abandonedmail/order/send', [
            'order_id' => $this->getOrderId(),
            'form_key' => $this->getFormKey(),
        ]);
    }

    /**
     * Get customer email.
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return (string) $this->getOrder()->getCustomerEmail();
    }

    /**
     * Check if Notify Invoice button should be shown (order has at least one invoice).
     *
     * @return bool
     */
    public function canShowNotifyInvoice(): bool
    {
        return (bool) $this->getOrder()->getInvoiceCollection()->getSize();
    }

    /**
     * Get Notify Invoice send mail URL.
     *
     * @return string
     */
    public function getNotifyInvoiceSendUrl(): string
    {
        return $this->backendUrl->getUrl('klever_orderactions/invoice/sendMail', [
            'order_id' => $this->getOrderId()
        ]);
    }

    /**
     * Get Notify Invoice update URL.
     *
     * @return string
     */
    public function getNotifyInvoiceUpdateUrl(): string
    {
        return $this->backendUrl->getUrl('klever_orderactions/invoice/update', [
            'order_id' => $this->getOrderId()
        ]);
    }

    /**
     * Get the latest invoice comment from order status history.
     *
     * @return string
     */
    public function getInvoiceComment(): string
    {
        $historyCollection = $this->getOrder()->getStatusHistoryCollection();
        foreach ($historyCollection as $history) {
            $comment = (string) $history->getComment();
            if (strpos($comment, 'Notify - Invoice - ') === 0) {
                /* Strip the prefix "Notify - Invoice - " and admin name suffix */
                $cleaned = substr($comment, strlen('Notify - Invoice - '));
                $byPos = strrpos($cleaned, ' - BY ');
                if ($byPos !== false) {
                    $cleaned = substr($cleaned, 0, $byPos);
                }
                return $cleaned;
            }
        }
        return '';
    }

    /**
     * Get Notify Invoice download PDF URL.
     *
     * @return string
     */
    public function getNotifyInvoiceDownloadUrl(): string
    {
        return $this->backendUrl->getUrl('klever_orderactions/invoice/downloadPdf', [
            'order_id' => $this->getOrderId()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Order Actions');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Order Actions');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
