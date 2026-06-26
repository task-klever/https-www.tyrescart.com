<?php

namespace WeltPixel\GA4\Model\Config\Source\RedditPixel;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TrackingEvents
 *
 * @package WeltPixel\GA4\Model\Config\Source\RedditPixel
 */
class TrackingEvents implements ArrayInterface
{
    const EVENT_PURCHASE = 'Purchase';
    const EVENT_VIEW_CONTENT = 'ViewContent';
    const EVENT_SEARCH = 'Search';
    const EVENT_ADD_TO_CART = 'AddToCart';
    const EVENT_ADD_TO_WISHLIST = 'AddToWishlist';
    const EVENT_SIGN_UP = 'SignUp';


    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::EVENT_PURCHASE,
                'label' => __('Purchase')
            ),
            array(
                'value' => self::EVENT_VIEW_CONTENT,
                'label' => __('ViewContent')
            ),
            array(
                'value' => self::EVENT_SEARCH,
                'label' => __('Search')
            ),
            array(
                'value' => self::EVENT_ADD_TO_CART,
                'label' => __('AddToCart')
            ),
            array(
                'value' => self::EVENT_ADD_TO_WISHLIST,
                'label' => __('AddToWishlist')
            ),
            array(
                'value' => self::EVENT_SIGN_UP,
                'label' => __('SignUp')
            )
        );
    }
}
