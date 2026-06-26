<?php
namespace WeltPixel\GA4\Block\TiktokPixel;

/**
 * Class \WeltPixel\GA4\Block\TiktokPixel\Common
 */
class Common extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \WeltPixel\GA4\Helper\TiktokPixelTracking
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \WeltPixel\GA4\Helper\TiktokPixelTracking $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \WeltPixel\GA4\Helper\TiktokPixelTracking $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isTiktokPixelTrackingEnabled()
    {
        return $this->helper->isTiktokPixelTrackingEnabled();
    }

    /**
     * @return string
     */
    public function getTiktokPixelTrackingCodeSnippet()
    {
        return $this->helper->getTiktokPixelTrackingCodeSnippet();
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldTiktokPixelEventBeTracked($eventName)
    {
        return $this->helper->shouldTiktokPixelEventBeTracked($eventName);
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote->getId();
    }

    /**
     * @return float|int
     */
    public function getCheckoutValue()
    {
        $quote = $this->checkoutSession->getQuote();
        $grandTotal = $quote->getGrandTotal() ?? 0;

        return $grandTotal;
    }

    /**
     * @return false|string
     */
    public function getCheckoutContents()
    {
        $quote = $this->checkoutSession->getQuote();
        $cartItems = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $cartItems[] = [
                'content_id' => $this->helper->getTiktokProductId($item->getProduct()),
                'content_type' => 'product',
                'content_name' => addslashes(str_replace('"','&quot;', $this->helper->getProductName($item->getProduct())))
            ];
        }

        return json_encode($cartItems);
    }

    /**
     * @return false|string
     */
    public function getProductsForPurchase($qty = false)
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        $products = [];
        if ($this->order) {
            foreach ($this->order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $productItemData = [
                    'content_id' => $this->helper->getTiktokProductId($product),
                    'content_type' => 'product',
                    'content_name' => addslashes(str_replace('"','&quot;', $this->helper->getProductName($product)))
                ];
                if ($qty) {
                    $productItemData['quantity'] = (int)$item->getQtyOrdered();
                }

                $products[] = $productItemData;
            }
        }

        return json_encode($products);
    }

    /**
     * @return float|int
     */
    public function getOrderValue()
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        return  floatval(number_format($this->order->getGrandtotal() ?? 0, 2, '.', ''));
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        return $this->order->getId();
    }
}
