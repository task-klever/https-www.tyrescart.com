<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Controller\Index;

use Hdweb\GenerateUrl\Api\GeneratorInterface;
use Hdweb\GenerateUrl\Helper\Data;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @param GeneratorInterface $generator
     * @param Data $helper
     * @param RawFactory $resultRawFactory
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly GeneratorInterface $generator,
        private readonly Data $helper,
        private readonly RawFactory $resultRawFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly RequestInterface $request,
    ) {
    }

    /**
     * @return Raw
     */
    public function execute(): Raw
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $filePath = $this->helper->getFilePath($storeId);

        // Check if the requested path matches the configured path
        $requestPath = trim($this->request->getPathInfo(), '/');
        $validPaths = [
            $filePath,
            $filePath . '.txt',
            $filePath . '/llms.txt',
        ];

        if (!in_array($requestPath, $validPaths, true)) {
            $result = $this->resultRawFactory->create();
            $result->setHttpResponseCode(404);
            return $result;
        }

        if (!$this->helper->isEnabled($storeId)) {
            $result = $this->resultRawFactory->create();
            $result->setHttpResponseCode(404);
            return $result;
        }

        $content = $this->generator->generate($storeId);

        $result = $this->resultRawFactory->create();
        $result->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $result->setContents($content);

        return $result;
    }
}

