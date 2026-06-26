<?php
namespace Klever\CustomCart\Controller\Update;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\JsonFactory;

class LastItemId extends Action
{
    protected $checkoutSession;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $quote = $this->checkoutSession->getQuote();
            $items = $quote->getAllVisibleItems();

            $lastItemId = null;
            if (!empty($items)) {
                $lastItem = end($items);
                $lastItemId = $lastItem->getId();
            }

            return $result->setData([
                'success' => true,
                'last_item_id' => $lastItemId
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}