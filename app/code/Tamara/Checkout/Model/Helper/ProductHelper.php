<?php

namespace Tamara\Checkout\Model\Helper;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ProductRepository;

class ProductHelper
{
    private $productRepository;
    private $imageHelper;

    public function __construct(
        ProductRepository $productRepository,
        ImageHelper $imageHelper
    ) {
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
    }

    public function getImageFromProductId($productId): string
    {
        try {                                                                                                    
            $product = $this->productRepository->getById($productId);                                              
                $image = $product->getImage();                                                                         
            if (empty($image) || $image === 'no_selection') {                                                      
                return '';                                                                                         
            }                                                                                                      
            return $this->imageHelper->init($product, 'small_image')                                        
                ->setImageFile($image)->getUrl();                                                           
        } catch (\Exception $e) {                                                                           
            return '';                                                                                      
        }                                                                                                   
    }
}