<?php

namespace WeltPixel\GA4\Observer\MetaPixel;

use Magento\Framework\Event\ObserverInterface;

class WishListAddProductObserver implements ObserverInterface
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
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;


    /**
     * @param \WeltPixel\GA4\Helper\MetaPixelTracking $metaPixelTrackingHelper
     * @param \WeltPixel\GA4\Helper\RedditPixelTracking $redditPixelTrackingHelper
     * @param \WeltPixel\GA4\Helper\TiktokPixelTracking $tiktokPixelTrackingHelper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(\WeltPixel\GA4\Helper\MetaPixelTracking $metaPixelTrackingHelper,
                                \WeltPixel\GA4\Helper\RedditPixelTracking $redditPixelTrackingHelper,
                                \WeltPixel\GA4\Helper\TiktokPixelTracking $tiktokPixelTrackingHelper,
                                \Magento\Customer\Model\Session $customerSession)
    {
        $this->metaPixelTrackingHelper = $metaPixelTrackingHelper;
        $this->redditPixelTrackingHelper = $redditPixelTrackingHelper;
        $this->tiktokPixelTrackingHelper = $tiktokPixelTrackingHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getData('product');

        if ($this->metaPixelTrackingHelper->isMetaPixelTrackingEnabled() && $this->metaPixelTrackingHelper->shouldMetaPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\MetaPixel\TrackingEvents::EVENT_ADD_TO_WISHLIST)) {
            $addToWishlistPushData = $this->metaPixelTrackingHelper->metaPixelAddToWishlistPushData($product);
            $initialAddToWishlistPushData =  $this->customerSession->getMetaPixelAddToWishlistData() ?? [];
            $initialAddToWishlistPushData[] = $addToWishlistPushData;
            $this->customerSession->setMetaPixelAddToWishlistData($initialAddToWishlistPushData);
        }

        if ($this->redditPixelTrackingHelper->isRedditPixelTrackingEnabled() && $this->redditPixelTrackingHelper->shouldRedditPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\RedditPixel\TrackingEvents::EVENT_ADD_TO_WISHLIST)) {
            $addToWishlistPushData = $this->redditPixelTrackingHelper->redditPixelAddToWishlistPushData($product);
            $initialAddToWishlistPushData =  $this->customerSession->getRedditPixelAddToWishlistData() ?? [];
            $initialAddToWishlistPushData[] = $addToWishlistPushData;
            $this->customerSession->setRedditPixelAddToWishlistData($initialAddToWishlistPushData);
        }

        if ($this->tiktokPixelTrackingHelper->isTiktokPixelTrackingEnabled() && $this->tiktokPixelTrackingHelper->shouldTiktokPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\TiktokPixel\TrackingEvents::EVENT_ADD_TO_WISHLIST)) {
            $addToWishlistPushData = $this->tiktokPixelTrackingHelper->tiktokPixelAddToWishlistPushData($product);
            $initialAddToWishlistPushData =  $this->customerSession->getTiktokPixelAddToWishlistData() ?? [];
            $initialAddToWishlistPushData[] = $addToWishlistPushData;
            $this->customerSession->setTiktokPixelAddToWishlistData($initialAddToWishlistPushData);
        }

        return $this;
    }
}
