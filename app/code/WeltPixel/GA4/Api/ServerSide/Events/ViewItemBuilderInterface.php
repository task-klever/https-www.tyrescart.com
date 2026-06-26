<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemBuilderInterface
{
    /**
     * @param $productId
     * @param $variant
     * @return null|ViewItemInterface
     */
    public function getViewItemEvent($productId, $variant = '');

    /**
     * @param $product
     * @param $productsArray
     * @return null|ViewItemInterface
     */
    public function getViewItemEventWithMultipleProducts($product, $productsArray);
}
