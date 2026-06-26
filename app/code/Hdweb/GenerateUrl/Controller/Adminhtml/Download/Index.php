<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Controller\Adminhtml\Download;

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
            $result->setContents('Extension is disabled.');
            return $result;
        }

        $content = $this->generator->generate($storeId);
        $filePath = $this->helper->getFilePath($storeId);
        $filename = $filePath . '.txt';

        $result = $this->resultRawFactory->create();
        $result->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $result->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $result->setContents($content);

        return $result;
    }
}

