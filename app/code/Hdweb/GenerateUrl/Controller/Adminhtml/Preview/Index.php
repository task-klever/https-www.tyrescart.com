<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Controller\Adminhtml\Preview;

use Hdweb\GenerateUrl\Api\GeneratorInterface;
use Hdweb\GenerateUrl\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Hdweb_GenerateUrl::config';

    /**
     * @param Context $context
     * @param GeneratorInterface $generator
     * @param Data $helper
     * @param RawFactory $resultRawFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        private readonly GeneratorInterface $generator,
        private readonly Data $helper,
        private readonly RawFactory $resultRawFactory,
        private readonly StoreManagerInterface $storeManager,
    ) {
        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute(): Raw
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
        }

        if (!$this->helper->isEnabled($storeId)) {
            $result = $this->resultRawFactory->create();
            $result->setContents('Extension is disabled. Please enable it in configuration.');
            return $result;
        }

        $content = $this->generator->generate($storeId);
        $filePath = $this->helper->getFilePath($storeId);
        $store = $this->storeManager->getStore($storeId);
        $baseUrl = $store->getBaseUrl();

        $previewContent = "=== LLMs.txt File Preview ===\n\n";
        $previewContent .= "Store: {$store->getName()} (ID: {$storeId})\n";
        $previewContent .= "File Path: {$filePath}\n";
        $previewContent .= "Access URL: {$baseUrl}{$filePath}.txt\n\n";
        $previewContent .= "=== Generated Content ===\n\n";
        $previewContent .= $content;

        $result = $this->resultRawFactory->create();
        $result->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $result->setContents($previewContent);

        return $result;
    }
}

