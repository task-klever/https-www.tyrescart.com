<?php

namespace Hdweb\Tyrefinder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Framework\View\LayoutFactory;

class QuickView extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $galleryReadHandler;
    protected $layoutFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        GalleryReadHandler $galleryReadHandler,
        LayoutFactory $layoutFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = ['status' => 'error', 'html' => ''];

        try {
            $productId = (int) $this->getRequest()->getParam('id');
            if (!$productId) {
                throw new \Exception('Product ID is required');
            }

            $product = $this->productRepository->getById($productId);
            $this->galleryReadHandler->execute($product);

            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(\Magento\Framework\View\Element\Template::class)
                ->setTemplate('Magento_Catalog::product/quickview.phtml')
                ->setData('product', $product);

            $response = [
                'status' => 'success',
                'html' => $block->toHtml()
            ];
        } catch (\Exception $e) {
            $response = ['status' => 'error', 'html' => '<p class="text-red-500 p-4">Product not found.</p>'];
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
