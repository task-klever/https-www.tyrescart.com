<?php
namespace WeltPixel\GA4\Block\RedditPixel;

/**
 * Class \WeltPixel\GA4\Block\RedditPixel\Common
 */
class Common extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \WeltPixel\GA4\Helper\RedditPixelTracking
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
     * @var int
     */
    protected $orderItemCount = 0;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \WeltPixel\GA4\Helper\RedditPixelTracking $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \WeltPixel\GA4\Helper\RedditPixelTracking $helper,
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
    public function isRedditPixelTrackingEnabled()
    {
        return $this->helper->isRedditPixelTrackingEnabled();
    }

    /**
     * @return string
     */
    public function getRedditPixelTrackingCode()
    {
        return $this->helper->getRedditPixelCodeSnippet();
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function shouldRedditPixelEventBeTracked($eventName)
    {
        return $this->helper->shouldRedditPixelEventBeTracked($eventName);
    }

    /**
     * @return false|string
     */
    public function getProductsForPurchase()
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        $products = [];
        if ($this->order) {
            foreach ($this->order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $products[] = [
                    'id' => $this->helper->getRedditProductId($product),
                    'name' => $this->helper->getProductName($product),
                    'category' => addslashes(str_replace('"','&quot;',$this->helper->getContentCategory($product->getCategoryIds())))
                ];

                $this->orderItemCount += (int)$item->getQtyOrdered();
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
     * @return string
     */
    public function getOrderTransactionId()
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        return  $this->order->getIncrementId();
    }

    /**
     * @return int
     */
    public function getOrderItemCount()
    {
        return $this->orderItemCount;
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
