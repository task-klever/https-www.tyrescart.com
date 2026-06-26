<?php
namespace WeltPixel\GA4\Observer\MetaPixel;

use Magento\Framework\Event\ObserverInterface;

class CartUpdateItemOptionsAfter implements ObserverInterface
{
    /**
     * @var \WeltPixel\GA4\Helper\MetaPixelTracking
     */
    protected $metaPixelTrackingHelper;

    /**
     * @var \WeltPixel\GA4\Helper\RedditPixelTracking
     */
    protected $redditPixelTrackingHelper;

    /**
     * @var \WeltPixel\GA4\Helper\TiktokPixelTracking
     */
    protected $tiktokPixelTrackingHelper;


    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;


    /**
     * @param \WeltPixel\GA4\Helper\MetaPixelTracking $metaPixelTrackingHelper
     * @param \WeltPixel\GA4\Helper\RedditPixelTracking $redditPixelTrackingHelper
     * @param \WeltPixel\GA4\Helper\TiktokPixelTracking $tiktokPixelTrackingHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     */
    public function __construct(
        \WeltPixel\GA4\Helper\MetaPixelTracking $metaPixelTrackingHelper,
        \WeltPixel\GA4\Helper\RedditPixelTracking $redditPixelTrackingHelper,
        \WeltPixel\GA4\Helper\TiktokPixelTracking $tiktokPixelTrackingHelper,
        \Magento\Checkout\Model\Session $_checkoutSession
    )
    {
        $this->metaPixelTrackingHelper = $metaPixelTrackingHelper;
        $this->redditPixelTrackingHelper = $redditPixelTrackingHelper;
        $this->tiktokPixelTrackingHelper = $tiktokPixelTrackingHelper;
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getData('item');

        if ($item->getQtyBeforeChange() != $item->getQty()) {
            $qtyChange =  $item->getQty() - $item->getQtyBeforeChange();
            if ($qtyChange != 0) {
                if ($qtyChange > 0) {
                    if ($this->metaPixelTrackingHelper->isMetaPixelTrackingEnabled() && $this->metaPixelTrackingHelper->shouldMetaPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\MetaPixel\TrackingEvents::EVENT_ADD_TO_CART)) {
                        $addToCartPushData = $this->metaPixelTrackingHelper->metaPixelAddToCartPushData($item->getProduct(), $qtyChange);
                        $initialAddTocartPushData = $this->_checkoutSession->getMetaPixelAddToCartData() ?? [];
                        $initialAddTocartPushData[] = $addToCartPushData;
                        $this->_checkoutSession->setMetaPixelAddToCartData($initialAddTocartPushData);
                    }
                    if ($this->redditPixelTrackingHelper->isRedditPixelTrackingEnabled() && $this->redditPixelTrackingHelper->shouldRedditPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\RedditPixel\TrackingEvents::EVENT_ADD_TO_CART)) {
                        $addToCartPushData = $this->redditPixelTrackingHelper->redditPixelAddToCartPushData($item->getProduct(), $qtyChange);
                        $initialAddTocartPushData =  $this->_checkoutSession->getRedditPixelAddToCartData() ?? [];
                        $initialAddTocartPushData[] = $addToCartPushData;
                        $this->_checkoutSession->setRedditPixelAddToCartData($initialAddTocartPushData);
                    }
                    if ($this->tiktokPixelTrackingHelper->isTiktokPixelTrackingEnabled() && $this->tiktokPixelTrackingHelper->shouldTiktokPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\TiktokPixel\TrackingEvents::EVENT_ADD_TO_CART)) {
                        $addToCartPushData = $this->tiktokPixelTrackingHelper->tiktokPixelAddToCartPushData($item->getProduct(), $qtyChange);
                        $initialAddTocartPushData =  $this->_checkoutSession->getTiktokPixelAddToCartData() ?? [];
                        $initialAddTocartPushData[] = $addToCartPushData;
                        $this->_checkoutSession->setTiktokPixelAddToCartData($initialAddTocartPushData);
                    }
                }
            }
        }

        return $this;
    }
}
