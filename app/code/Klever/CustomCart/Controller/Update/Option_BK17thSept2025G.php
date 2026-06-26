<?php
namespace Klever\CustomCart\Controller\Update;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\Result\JsonFactory;

class Option extends Action
{
    protected $cart;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Cart $cart,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $data = json_decode($this->getRequest()->getContent(), true);
        $itemId = $data['item_id'] ?? null;
        $checked = $data['checked'] ?? false;
        $optionId = $data['option_id'] ?? null;

        $result = ['success' => false];

        if (!$itemId || !$optionId) {
            return $this->resultJsonFactory->create()->setData($result);
        }

        $quote = $this->cart->getQuote();
        $item = $quote->getItemById($itemId);

        if ($item) {
            $buyRequest = $item->getBuyRequest();
            $options = $buyRequest->getData('options') ?? [];

            if ($checked) {
                $options[$optionId] = 'Yes';
            } else {
                if (isset($options[$optionId])) {
                    unset($options[$optionId]);
                }
            }

            $buyRequest->setData('options', $options);
            try {
                $item->updateItem($buyRequest);
                $quote->collectTotals()->save();
                $result['success'] = true;
            } catch (\Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
