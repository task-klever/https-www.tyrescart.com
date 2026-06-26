<?php

declare(strict_types=1);

namespace Klever\AbandonedMail\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;

class AddAbandonedMailButton
{
    private Order $order;

    public function __construct(
        Order $order
    ) {
        $this->order = $order;
    }

    public function beforeSetLayout(View $view): ?array
    {
        $orderId = $view->getOrderId();

        if (!$orderId) {
            return null;
        }

        $order = $this->order->load($orderId);
        $orderStatus = $order->getStatus();

        $allowedStatuses = ['expired', 'cancel', 'pending', 'pending_payment'];

        if (in_array(strtolower($orderStatus), $allowedStatuses, true)) {
            $customerEmail = $order->getCustomerEmail();
            $isAbandonedSend = (int) $order->getData('is_abandoned_send');
            $abandonedSendAt = $order->getData('abandoned_send_at');
            $isRestricted = false;

            if ($isAbandonedSend && $abandonedSendAt) {
                $sendTime = strtotime($abandonedSendAt);
                $currentTime = time();
                $hoursPassed = ($currentTime - $sendTime) / 3600;

                if ($hoursPassed < 24) {
                    $isRestricted = true;
                }
            }

            $formKey = $view->getFormKey();
            $sendUrl = $view->getUrl('abandonedmail/order/send', [
                'order_id' => $orderId,
                'form_key' => $formKey,
            ]);
            $escapedEmail = $view->escapeJs($customerEmail);
            $escapedUrl = $view->escapeUrl($sendUrl);
            $restrictedFlag = $isRestricted ? 'true' : 'false';

            $onclickJs = "require(['Klever_AbandonedMail/js/abandoned-mail-modal'], function(abandonedMail) { " .
                "abandonedMail.send('{$escapedEmail}', '{$escapedUrl}', {$restrictedFlag}); }); return false;";

            $view->addButton(
                'notify_abandoned_mail',
                [
                    'label' => __('Notify Abandoned Mail'),
                    'class' => 'notify-abandoned-mail-button',
                    'onclick' => $onclickJs,
                ]
            );
        }

        return null;
    }
}

