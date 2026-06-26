<?php
namespace WeltPixel\GA4\Observer\MetaPixel;

use Magento\Framework\Event\ObserverInterface;

class CheckoutCartAddProductObserver implements ObserverInterface
{
    /**
     * @var \WeltPixel\GA4\Helper\MetaPixelTracking
     */
    protected $metaPixelHelper;

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
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;


    /**
     * @param \WeltPixel\GA4\Helper\MetaPixelTracking $metaPixelHelper
     * @param \WeltPixel\GA4\Helper\RedditPixelTracking $redditPixelTrackingHelper
     * @param \WeltPixel\GA4\Helper\TiktokPixelTracking $tiktokPixelTrackingHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(\WeltPixel\GA4\Helper\MetaPixelTracking $metaPixelHelper,
                                \WeltPixel\GA4\Helper\RedditPixelTracking $redditPixelTrackingHelper,
                                \WeltPixel\GA4\Helper\TiktokPixelTracking $tiktokPixelTrackingHelper,
                                \Magento\Checkout\Model\Session $_checkoutSession,
                                \Magento\Framework\Locale\ResolverInterface $localeResolver)
    {
        $this->metaPixelHelper = $metaPixelHelper;
        $this->redditPixelTrackingHelper = $redditPixelTrackingHelper;
        $this->tiktokPixelTrackingHelper = $tiktokPixelTrackingHelper;
        $this->_checkoutSession = $_checkoutSession;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getData('product');
        $request = $observer->getData('request');

        $params = $request->getParams();

        if (isset($params['qty'])) {
            $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                ['locale' => $this->localeResolver->getLocale()]
            );
            $qty = $filter->filter($params['qty']);
        } else {
            $qty = 1;
        }

        if ($this->metaPixelHelper->isMetaPixelTrackingEnabled() && $this->metaPixelHelper->shouldMetaPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\MetaPixel\TrackingEvents::EVENT_ADD_TO_CART)) {
            $addToCartPushData = $this->metaPixelHelper->metaPixelAddToCartPushData($product, $qty);
            $initialAddTocartPushData = $this->_checkoutSession->getMetaPixelAddToCartData() ?? [];
            $initialAddTocartPushData[] = $addToCartPushData;
            $this->_checkoutSession->setMetaPixelAddToCartData($initialAddTocartPushData);
        }
        if ($this->redditPixelTrackingHelper->isRedditPixelTrackingEnabled() && $this->redditPixelTrackingHelper->shouldRedditPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\RedditPixel\TrackingEvents::EVENT_ADD_TO_CART)) {
            $addToCartPushData = $this->redditPixelTrackingHelper->redditPixelAddToCartPushData($product, $qty);
            $initialAddTocartPushData =  $this->_checkoutSession->getRedditPixelAddToCartData() ?? [];
            $initialAddTocartPushData[] = $addToCartPushData;
            $this->_checkoutSession->setRedditPixelAddToCartData($initialAddTocartPushData);
        }
        if ($this->tiktokPixelTrackingHelper->isTiktokPixelTrackingEnabled() && $this->tiktokPixelTrackingHelper->shouldTiktokPixelEventBeTracked(\WeltPixel\GA4\Model\Config\Source\TiktokPixel\TrackingEvents::EVENT_ADD_TO_CART)) {
            $addToCartPushData = $this->tiktokPixelTrackingHelper->tiktokPixelAddToCartPushData($product, $qty);
            $initialAddTocartPushData =  $this->_checkoutSession->getTiktokPixelAddToCartData() ?? [];
            $initialAddTocartPushData[] = $addToCartPushData;
            $this->_checkoutSession->setTiktokPixelAddToCartData($initialAddTocartPushData);
        }

        return $this;
    }
}
