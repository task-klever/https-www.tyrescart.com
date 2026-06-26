<?php
namespace Klever\CustomCart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

class CollectTotalsAfterCartUpdate implements ObserverInterface
{
    protected LoggerInterface $logger;
    protected RequestInterface $request;
    protected CheckoutSession $checkoutSession;

    public function __construct(
        LoggerInterface $logger,
        RequestInterface $request,
        CheckoutSession $checkoutSession
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote || !$quote->getId()) {
            $this->logger->info("❌ No quote found in session, skipping update.");
            return;
        }

        $infoData = $this->request->getParam('cart', []);
        $this->logger->info('📥 POST DATA (cart): ' . json_encode($infoData));

        if (empty($infoData)) {
            $this->logger->info("⚠️ No cart data in POST, skipping update.");
            return;
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            $itemId = $item->getId();
            $itemCartData = $infoData[$itemId] ?? null;

            if (!$itemCartData) {
                $this->logger->info("ℹ️ Item {$itemId} not found in POST, skipping.");
                continue;
            }

            // Get warranty value from POST data (default '0')
            $warrantyValue = $itemCartData['options'][1][0] ?? '0';
            $warrantyLabel = ($warrantyValue === '1') ? 'Yes' : 'No';
            $this->logger->info("🔄 Item {$itemId} - Warranty Value: {$warrantyValue}");

            // Build product_options dynamically
            $productOptions = [
                'info_buyRequest' => [
                    'options' => ['1' => $warrantyValue]
                ],
                'options' => [
                    [
                        'label' => 'Warranty',
                        'value' => $warrantyLabel,
                        'option_id' => 1,
                        'option_value' => $warrantyValue
                    ]
                ]
            ];

            // Save product options as JSON to avoid array-to-string error
            $item->setData('product_options', json_encode($productOptions));

            // Update price based on warranty value
            if ($warrantyValue === '1') {
                $customPrice = 460; // static for now, later make dynamic from product option price
                $item->setCustomPrice($customPrice);
                $item->setOriginalCustomPrice($customPrice);
                $item->setPrice($customPrice);
                $this->logger->info("💰 Item {$itemId} - Warranty applied, price set to {$customPrice}");
            } else {
                $basePrice = $item->getProduct()->getFinalPrice();
                $item->setCustomPrice($basePrice);
                $item->setOriginalCustomPrice($basePrice);
                $item->setPrice($basePrice);
                $this->logger->info("💸 Item {$itemId} - Warranty removed, price reset to base price: {$basePrice}");
            }

            $item->calcRowTotal();
            $item->getProduct()->setIsSuperMode(true);
        }

        $quote->collectTotals()->save();
        $this->logger->info("✅ Totals re-collected and quote saved.");
    }
}