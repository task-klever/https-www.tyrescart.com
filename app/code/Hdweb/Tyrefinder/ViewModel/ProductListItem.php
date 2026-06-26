<?php

namespace Hdweb\Tyrefinder\ViewModel;

use Hyva\Theme\ViewModel\ProductListItem as OriginalProductListItem;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\AbstractBlock;

class ProductListItem extends OriginalProductListItem
{
    public function getBundleItemHtml(
        Product $frontProduct,
        Product $rearProduct,
        AbstractBlock $parentBlock,
        string $viewMode,
        string $templateType,
        string $imageDisplayArea,
        bool $showDescription
    ): string {
        /** @var AbstractBlock $itemRendererBlock */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');
        
        
        $itemRendererBlock = $layout->getBlock('product_list_item_bundle');

        if (! $itemRendererBlock) {
            return '';
        }

        return $this->getBundleItemHtmlWithRenderer(
            $itemRendererBlock,
            $frontProduct,
            $rearProduct,
            $parentBlock,
            $viewMode,
            $templateType,
            $imageDisplayArea,
            $showDescription
        );
    }

    public function getBundleItemHtmlWithRenderer(
        AbstractBlock $itemRendererBlock,
        Product $frontProduct,
        Product $rearProduct,
        AbstractBlock $parentBlock,
        string $viewMode,
        string $templateType,
        string $imageDisplayArea,
        bool $showDescription
    ): string {
        // Careful! Temporal coupling!
        // First the values on the block need to be set, then the cache key info array can be created.
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$blockCache = $objectManager->get('Hyva\Theme\ViewModel\BlockCache');

        $itemRendererBlock->setData('frontProduct', $frontProduct)
        				  ->setData('rearProduct', $rearProduct)
                          ->setData('view_mode', $viewMode)
                          ->setData('item_relation_type', $parentBlock->getData('item_relation_type'))
                          ->setData('image_display_area', $imageDisplayArea)
                          ->setData('show_description', $showDescription)
                          ->setData('position', $parentBlock->getPositioned())
                          ->setData('pos', $parentBlock->getPositioned())
                          ->setData('template_type', $templateType)
                          /* ->setData('cache_lifetime', 3600)
                          ->setData('front_cache_tags', $frontProduct->getIdentities())
                          ->setData('rear_cache_tags', $rearProduct->getIdentities()) */;

        //$itemCacheKeyInfo = $this->getItemCacheKeyInfo($frontProduct, $parentBlock, $viewMode, $templateType);
        //$itemCacheKeyInfo = $this->getItemCacheKeyInfo($rearProduct, $parentBlock, $viewMode, $templateType);
        //$itemRendererBlock->setData('cache_key', $blockCache->hashCacheKeyInfo($itemCacheKeyInfo));

        foreach (($itemRendererBlock->getData('additional_item_renderer_processors') ?? []) as $processor) {
            if (method_exists($processor, 'beforeListItemToHtml')) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                call_user_func([$processor, 'beforeListItemToHtml'], $itemRendererBlock, $frontProduct);
                call_user_func([$processor, 'beforeListItemToHtml'], $itemRendererBlock, $rearProduct);
            }
        }

        return $itemRendererBlock->toHtml();
    }

    public function getBundleListHtml(
        array $bundleCollection,
        AbstractBlock $parentBlock,
        string $viewMode,
        string $templateType,
        string $imageDisplayArea,
        bool $showDescription
    ): string {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');
        $itemRendererBlock = $layout->getBlock('product_list_item_bundle');

        if (!$itemRendererBlock) {
            return '';
        }

        $itemRendererBlock->setData('bundleItems', $bundleCollection)
                          ->setData('view_mode', $viewMode)
                          ->setData('item_relation_type', $parentBlock->getData('item_relation_type'))
                          ->setData('image_display_area', $imageDisplayArea)
                          ->setData('show_description', $showDescription)
                          ->setData('position', $parentBlock->getPositioned())
                          ->setData('pos', $parentBlock->getPositioned())
                          ->setData('template_type', $templateType)
                          ->setData('render_mode', 'batch');

        return $itemRendererBlock->toHtml();
    }
}
