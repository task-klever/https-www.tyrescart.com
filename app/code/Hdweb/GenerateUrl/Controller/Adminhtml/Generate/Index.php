<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Controller\Adminhtml\Generate;

use Hdweb\GenerateUrl\Api\GeneratorInterface;
use Hdweb\GenerateUrl\Helper\Data;
use Hdweb\GenerateUrl\Model\FileService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\Storage\WriterInterface;
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
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param FileService $fileService
     */
    public function __construct(
        Context $context,
        private readonly GeneratorInterface $generator,
        private readonly Data $helper,
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager,
        private readonly FileService $fileService,
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        
        if (!$storeId) {
            $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
        }

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('admin/system_config/edit/section/generateurl', ['store' => $storeId]);

        if (!$this->helper->isEnabled($storeId)) {
            $this->messageManager->addErrorMessage(__('Extension is disabled. Please enable it first.'));
            return $redirect;
        }

        try {
            // Generate the content
            $content = $this->generator->generate($storeId);

            if (empty($content)) {
                throw new \Exception(__('Generated content is empty. Please check your configuration.'));
            }

            // Get filename from configuration
            $filePath = $this->helper->getFilePath($storeId);
            $filename = $filePath . '.txt';

            // Save file to pub directory
            $fileSaved = $this->fileService->saveFile($filePath, $content, $storeId);

            if (!$fileSaved) {
                throw new \Exception(__('Failed to save file to pub directory. Please check file permissions.'));
            }

            // Save last generated timestamp
            $timestamp = date('Y-m-d H:i:s');
            $configPath = Data::XML_PATH_LAST_GENERATED_AT;
            
            if ($storeId) {
                $this->configWriter->save($configPath, $timestamp, 'stores', $storeId);
            } else {
                $this->configWriter->save($configPath, $timestamp, 'default', 0);
            }

            $this->messageManager->addSuccessMessage(
                __('File generated successfully at %1. File saved as: %2', $timestamp, $filename)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect;
    }
}

