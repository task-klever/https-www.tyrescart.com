<?php
namespace Klever\CustomCart\Controller\Update;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;



class Option extends Action
{
    protected $resultJsonFactory;
    protected $cart;
    protected $productRepository;
    protected $formKeyValidator;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

       



     /*  // ✅ Validate form key
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'success' => false,
                'message' => __('Invalid form key. Please refresh the page and try again.')
            ]);
        }
*/
        try {
            // Parse JSON body
            $rawBody = $this->getRequest()->getContent();
            $data = json_decode($rawBody, true);

            if (!$data) {
                throw new LocalizedException(__('Invalid request payload.'));
            }
/*
            // ✅ Validate form key
            if (empty($data['form_key']) || !$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key. Please refresh the page and try again.'));
            }
*/
            if (empty($data['item_id']) || empty($data['options'])) {
                throw new LocalizedException(__('Invalid data received.'));
            }

            $itemId = (int)$data['item_id'];
            $options = $data['options'];

            $quote = $this->cart->getQuote();
            $item = $quote->getItemById($itemId);

            if (!$item) {
                throw new LocalizedException(__('Quote item not found.'));
            }

            $productId = $item->getProduct()->getId();
            $product = $this->productRepository->getById($productId);

            // Build buyRequest with updated options
            $buyRequest = new \Magento\Framework\DataObject([
                'product' => $productId,
                'qty'     => $item->getQty(),
                'options' => $options
            ]);

            $quote->updateItem($itemId, $buyRequest);
            $quote->collectTotals()->save();

            return $result->setData([
                'success' => true,
                'message' => __('Item updated successfully.')
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
