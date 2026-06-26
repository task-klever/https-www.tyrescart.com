<?php
namespace WeltPixel\GA4\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * Gtm section
 */
class Gtm extends \Magento\Framework\DataObject implements SectionSourceInterface
{

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * Constructor
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ManagerInterface $eventManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        ManagerInterface $eventManager,
        array $data = []
    )
    {
        parent::__construct($data);
        $this->jsonHelper = $jsonHelper;
        $this->_checkoutSession = $_checkoutSession;
        $this->customerSession = $customerSession;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {

        $data = [];
        $metaPixelData = [];
        $redditPixelData = [];
        $tiktokPixelData = [];

        /** AddToCart data verifications */
        if ($this->_checkoutSession->getGA4AddToCartData()) {
            $data[] = $this->_checkoutSession->getGA4AddToCartData();
        }

        $this->_checkoutSession->setGA4AddToCartData(null);

        /** RemoveFromCart data verifications */
        if ($this->_checkoutSession->getGA4RemoveFromCartData()) {
            $data[] = $this->_checkoutSession->getGA4RemoveFromCartData();
        }

        $this->_checkoutSession->setGA4RemoveFromCartData(null);

        /** Checkout Steps data verifications */
        if ($this->_checkoutSession->getGA4CheckoutOptionsData()) {
            $checkoutOptions = $this->_checkoutSession->getGA4CheckoutOptionsData();
            foreach ($checkoutOptions as $options) {
                $data[] = $options;
            }
        }
        $this->_checkoutSession->setGA4CheckoutOptionsData(null);

        /** Add To Wishlist Data */
        if ($this->customerSession->getGA4AddToWishListData()) {
            $data[] = $this->customerSession->getGA4AddToWishListData();
        }
        $this->customerSession->setGA4AddToWishListData(null);

        /** Add To Compare Data */
        if ($this->customerSession->getGA4AddToCompareData()) {
            $data[] = $this->customerSession->getGA4AddToCompareData();
        }
        $this->customerSession->setGA4AddToCompareData(null);

        /** Add Signup Data */
        if ($this->customerSession->getGA4SignupData()) {
            $data[] = $this->customerSession->getGA4SignupData();
        }
        $this->customerSession->setGA4SignupData(null);

        /** Add Login Data */
        if ($this->customerSession->getGA4LoginData()) {
            $data[] = $this->customerSession->getGA4LoginData();
        }
        $this->customerSession->setGA4LoginData(null);


        /** MetaPixel Add To Cart  */
        if ($this->_checkoutSession->getMetaPixelAddToCartData()) {
            foreach ($this->_checkoutSession->getMetaPixelAddToCartData() as $metaPixelAddToCartData) {
                $metaPixelData[] = $metaPixelAddToCartData;
            }
        }
        $this->_checkoutSession->setMetaPixelAddToCartData(null);

        /** MetaPixel Add To Wishlist  */
        if ($this->customerSession->getMetaPixelAddToWishlistData()) {
            foreach ($this->customerSession->getMetaPixelAddToWishlistData() as $metaPixelAddToWishlistData) {
                $metaPixelData[] = $metaPixelAddToWishlistData;
            }
        }
        $this->customerSession->setMetaPixelAddToWishlistData(null);


        /** Reddit Pixel SignUp  */
        if ($this->customerSession->getRedditPixelSignupData()) {
            $redditPixelData[] = $this->customerSession->getRedditPixelSignupData();
        }
        $this->customerSession->setRedditPixelSignupData(null);

        /** Reddit Pixel Add To Wishlist  */
        if ($this->customerSession->getRedditPixelAddToWishlistData()) {
            foreach ($this->customerSession->getRedditPixelAddToWishlistData() as $redditPixelAddToWishlistData) {
                $redditPixelData[] = $redditPixelAddToWishlistData;
            }
        }
        $this->customerSession->setRedditPixelAddToWishlistData(null);

        /** Reddit Pixel Add To Cart  */
        if ($this->_checkoutSession->getRedditPixelAddToCartData()) {
            foreach ($this->_checkoutSession->getRedditPixelAddToCartData() as $redditPixelAddToCartData) {
                $redditPixelData[] = $redditPixelAddToCartData;
            }
        }
        $this->_checkoutSession->setRedditPixelAddToCartData(null);

        /** Tiktok Pixel Add To Cart  */
        if ($this->_checkoutSession->getTiktokPixelAddToCartData()) {
            foreach ($this->_checkoutSession->getTiktokPixelAddToCartData() as $tiktokPixelAddToCartData) {
                $tiktokPixelData[] = $tiktokPixelAddToCartData;
            }
        }
        $this->_checkoutSession->setTiktokPixelAddToCartData(null);

        /** Tiktok Pixel Add To Wishlist  */
        if ($this->customerSession->getTiktokPixelAddToWishlistData()) {
            foreach ($this->customerSession->getTiktokPixelAddToWishlistData() as $tiktokPixelAddToWishlistData) {
                $tiktokPixelData[] = $tiktokPixelAddToWishlistData;
            }
        }
        $this->customerSession->setTiktokPixelAddToWishlistData(null);


        $ga4SectionData = [
            'datalayer' => $this->jsonHelper->jsonEncode($data),
            'metapixel' => $this->jsonHelper->jsonEncode($metaPixelData),
            'redditpixel' => $this->jsonHelper->jsonEncode($redditPixelData),
            'tiktokpixel' => $this->jsonHelper->jsonEncode($tiktokPixelData)
        ];

        $ga4SectionDataObject = new \Magento\Framework\DataObject(['section_data' => $ga4SectionData ]);

        $this->eventManager->dispatch('weltpixel_ga4_section_data', ['ga4_section_data' => $ga4SectionDataObject]);

        return $ga4SectionDataObject->getData('section_data');

    }
}
