<?php
namespace Klever\CustomCart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class UpdateCartOptions implements ObserverInterface
{
    protected QuoteRepository $quoteRepository;
    protected RequestInterface $request;
    protected LoggerInterface $logger;

    public function __construct(
        QuoteRepository $quoteRepository,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Checkout\Model\Cart $cart */
            $cart = $observer->getCart();
            $quote = $cart->getQuote();

            $cartData = $this->request->getParam('cart', []);
            if (empty($cartData)) {
                return;
            }

            foreach ($cartData as $itemId => $itemData) {
                $quoteItem = $quote->getItemById($itemId);
                if (!$quoteItem) {
                    continue;
                }

                if (isset($itemData['options'])) {
                    $productOptions = $quoteItem->getOptionByCode('additional_options');
                    $options = $productOptions ? @unserialize($productOptions->getValue()) : [];

                    foreach ($itemData['options'] as $optionId => $value) {
                        $options[$optionId] = $value;
                    }

                    $quoteItem->addOption([
                        'code' => 'additional_options',
                        'value' => serialize($options)
                    ]);
                }

                // update quantity if provided
                if (isset($itemData['qty'])) {
                    $quoteItem->setQty((float) $itemData['qty']);
                }
            }

            $quote->collectTotals();
            $this->quoteRepository->save($quote);

        } catch (\Exception $e) {
            $this->logger->error('UpdateCartOptions error: ' . $e->getMessage());
        }
    }
}