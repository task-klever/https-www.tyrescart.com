<?php

declare(strict_types=1);

namespace Klever\AbandonedMail\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Send extends Action
{
    protected JsonFactory $resultJsonFactory;

    private OrderRepositoryInterface $orderRepository;

    private LoggerInterface $logger;

    private TransportBuilder $transportBuilder;

    private StateInterface $inlineTranslation;

    private StoreManagerInterface $storeManager;

    private ScopeConfigInterface $scopeConfig;

    private AuthSession $authSession;

    private OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OrderRepositoryInterface $orderRepository,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AuthSession $authSession,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->authSession = $authSession;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->logger = $logger;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Magento_Sales::actions');
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $orderId = (int) $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Order ID is required.'),
            ]);
        }

        try {
            $order = $this->orderRepository->get($orderId);
            $orderStatus = $order->getStatus();
            $allowedStatuses = ['expired', 'cancel', 'pending', 'pending_payment'];

            if (!in_array(strtolower($orderStatus), $allowedStatuses, true)) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Abandoned mail can only be sent for orders with status: Expired, Cancel, Pending, or Pending Payment.'),
                ]);
            }

            $isAbandonedSend = (int) $order->getData('is_abandoned_send');
            $abandonedSendAt = $order->getData('abandoned_send_at');

            if ($isAbandonedSend && $abandonedSendAt) {
                $sendTime = strtotime($abandonedSendAt);
                $currentTime = time();
                $hoursPassed = ($currentTime - $sendTime) / 3600;

                if ($hoursPassed < 24) {
                    $remainingHours = 24 - $hoursPassed;
                    $remainingTime = round($remainingHours, 1);

                    return $resultJson->setData([
                        'success' => false,
                        'message' => __('Abandoned mail already sent. Please try again after some time.'),
                        'restricted' => true,
                    ]);
                }
            }

            $customerEmail = $order->getCustomerEmail();
            $customerName = $order->getCustomerName() ?: $order->getBillingAddress()->getName();
            $storeId = $order->getStoreId();

            if (!$customerEmail) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Customer email is not available for this order.'),
                ]);
            }

            try {
                $this->inlineTranslation->suspend();

                $this->storeManager->setCurrentStore($storeId);

                $orderItems = [];
                $productNames = [];

                $allItems = $order->getAllVisibleItems();

                foreach ($allItems as $item) {
                    $product = $item->getProduct();

                    if ($product && $product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                        $productName = $item->getName();

                        if (!in_array($productName, $productNames, true)) {
                            $productNames[] = $productName;
                            $orderItems[] = [
                                'name' => $productName,
                            ];
                        }
                    }
                }

                $productNamesCommaSeparated = implode(', ', $productNames);

                $templateVars = [
                    'customer_name' => $customerName,
                    'order_items' => $orderItems,
                    'product_names' => $productNamesCommaSeparated,
                ];

                $templateOptions = [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ];

                $fromEmail = $this->scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $fromName = $this->scopeConfig->getValue(
                    'trans_email/ident_general/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );

                if (!$fromEmail || !$fromName) {
                    throw new \Exception('Email sender configuration is not set properly.');
                }

                $from = [
                    'email' => $fromEmail,
                    'name' => $fromName,
                ];

                $this->transportBuilder
                    ->setTemplateIdentifier(26)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($customerEmail, $customerName)
                    ->getTransport()
                    ->sendMessage();

                $this->inlineTranslation->resume();

                $order->setData('is_abandoned_send', 1);
                $order->setData('abandoned_send_at', date('Y-m-d H:i:s'));
                $this->orderRepository->save($order);

                $adminUser = $this->authSession->getUser();
                $adminName = $adminUser ? ($adminUser->getFirstname() . ' ' . $adminUser->getLastname()) : 'Admin';
                $currentDateTime = date('Y-m-d H:i:s');

                $commentText = sprintf(
                    'Abandoned Mail notification sent to customer by Admin %s on %s.',
                    $adminName,
                    $currentDateTime
                );

                $comment = $order->addStatusHistoryComment($commentText);
                $comment->setIsCustomerNotified(false);
                $this->orderStatusHistoryRepository->save($comment);

                $this->logger->info(
                    'Abandoned mail notification sent',
                    [
                        'order_id' => $orderId,
                        'customer_email' => $customerEmail,
                        'customer_name' => $customerName,
                        'admin_name' => $adminName,
                    ]
                );

                return $resultJson->setData([
                    'success' => true,
                    'message' => __('Abandoned mail reminder has been sent to %1.', $customerEmail),
                ]);
            } catch (\Exception $emailException) {
                $this->inlineTranslation->resume();

                $this->logger->error(
                    'Error sending abandoned mail email',
                    [
                        'order_id' => $orderId,
                        'customer_email' => $customerEmail,
                        'error' => $emailException->getMessage(),
                    ]
                );

                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Failed to send email: %1', $emailException->getMessage()),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Error sending abandoned mail',
                [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]
            );

            return $resultJson->setData([
                'success' => false,
                'message' => __('An error occurred while sending the abandoned mail: %1', $e->getMessage()),
            ]);
        }
    }
}

