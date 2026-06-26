<?php

declare(strict_types=1);

namespace Klever\AbandonedMail\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class ResetAbandonedFlag
{
    private CollectionFactory $orderCollectionFactory;

    private OrderRepositoryInterface $orderRepository;

    private LoggerInterface $logger;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('is_abandoned_send', 1);
            $collection->addFieldToFilter('abandoned_send_at', ['notnull' => true]);

            $resetCount = 0;
            $currentTime = time();

            foreach ($collection as $order) {
                $abandonedSendAt = $order->getData('abandoned_send_at');

                if ($abandonedSendAt) {
                    $sendTime = strtotime($abandonedSendAt);
                    $hoursPassed = ($currentTime - $sendTime) / 3600;

                    if ($hoursPassed >= 24) {
                        $order->setData('is_abandoned_send', 0);
                        $order->setData('abandoned_send_at', null);
                        $this->orderRepository->save($order);
                        $resetCount++;
                    }
                }
            }

            if ($resetCount > 0) {
                $this->logger->info(
                    'Reset abandoned mail flags',
                    [
                        'count' => $resetCount,
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Error resetting abandoned mail flags',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}

