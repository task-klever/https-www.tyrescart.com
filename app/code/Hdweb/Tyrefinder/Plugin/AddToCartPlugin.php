<?php

namespace Hdweb\Tyrefinder\Plugin;

use Magento\Checkout\Controller\Cart\Add as CartAddController;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Store\Model\StoreManagerInterface;

class AddToCartPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $storeManager;

    protected $cart;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CustomerCart $cart,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
    }

    /**
     * Plugin method to be executed before the original execute method.
     *
     * @param CartAddController $subject
     * @param callable $proceed
     */
    public function aroundExecute(
        CartAddController $subject,
        callable $proceed
    ) {
        // Your custom code to be executed before the original execute method
        $params = $subject->getRequest()->getParams();

        // Your custom code to add the bundle product
        if (isset($params['bundleproduct']) && !empty($params['bundleproduct'])) {
            $storeId = $this->storeManager->getStore()->getId();
            $bundleproductId = $params['bundleproduct'];
            $bundleproduct = $this->productRepository->getById($bundleproductId, false, $storeId);
            $params['qty'] = $params['bundleqty'];
            $this->cart->addProduct($bundleproduct, $params);
        }

        // Call the original execute method
        $result = $proceed();

        // Your custom code to be executed after the original execute method

        return $result;
    }
}
