<?php
namespace Vendor\Module\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class UpdateCartOptionsObserver implements ObserverInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $cart = $observer->getCart();
        $infoData = $observer->getInfo()->getData(); // posted cart data
        $quote = $cart->getQuote();

        foreach ($quote->getAllVisibleItems() as $item) {
            if (isset($post['cart'][$itemId]['options'])) {
    foreach ($post['cart'][$itemId]['options'] as $optionId => $value) {
        $this->logger->info("✅ Item {$itemId} - posted option {$optionId} => {$value}");

        // ✅ Store option to item so Magento will have it on reload
        $productOptions = $item->getProductOptions() ?: [];
        $productOptions['options'][$optionId] = [
            'label' => 'Warranty',
            'value' => $value,
            'option_id' => $optionId,
            'option_value' => $value === 'Yes' ? '1' : '0',
        ];
        $item->setProductOptions($productOptions);

        // ✅ Custom price logic
        if ($value === 'Yes') {
            $this->logger->info("💵 Set custom price 460 for item {$itemId}");
            $item->setCustomPrice(460);
            $item->setOriginalCustomPrice(460);
        } else {
            $this->logger->info("💵 Resetting custom price for item {$itemId}");
            $item->setCustomPrice(null);
            $item->setOriginalCustomPrice(null);
        }
        $item->getProduct()->setIsSuperMode(true);
    }
}
        }

        $quote->collectTotals()->save();
    }
}