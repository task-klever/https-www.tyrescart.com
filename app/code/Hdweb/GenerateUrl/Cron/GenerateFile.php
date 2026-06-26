<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Cron;

use Hdweb\GenerateUrl\Api\GeneratorInterface;
use Hdweb\GenerateUrl\Helper\Data;
use Hdweb\GenerateUrl\Model\FileService;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class GenerateFile
{
    /**
     * @param GeneratorInterface $generator
     * @param Data $helper
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param FileService $fileService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly GeneratorInterface $generator,
        private readonly Data $helper,
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager,
        private readonly FileService $fileService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute(): void
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $storeId = (int) $store->getId();

            if (!$this->helper->isEnabled($storeId)) {
                continue;
            }

            $frequency = $this->helper->getConfigValue(
                Data::XML_PATH_GENERATION_FREQUENCY,
                $storeId
            );

            if ($frequency !== 'custom_cron' && $frequency !== 'hourly' && $frequency !== 'daily') {
                continue;
            }

            try {
                $content = $this->generator->generate($storeId);

                if (empty($content)) {
                    $this->logger->warning(
                        __('LLMs.txt generation failed for store %1: Empty content', $storeId)
                    );
                    continue;
                }

                // Get filename from configuration
                $filePath = $this->helper->getFilePath($storeId);

                // Save file to pub directory
                $fileSaved = $this->fileService->saveFile($filePath, $content, $storeId);

                if (!$fileSaved) {
                    $this->logger->error(
                        __('Failed to save LLMs.txt file to pub directory for store %1', $storeId)
                    );
                    continue;
                }

                // Save last generated timestamp
                $timestamp = date('Y-m-d H:i:s');
                $configPath = Data::XML_PATH_LAST_GENERATED_AT;

                if ($storeId) {
                    $this->configWriter->save($configPath, $timestamp, 'stores', $storeId);
                } else {
                    $this->configWriter->save($configPath, $timestamp, 'default', 0);
                }

                $this->logger->info(
                    __('LLMs.txt file generated and saved successfully for store %1 at %2. File: %3.txt', 
                        $storeId, 
                        $timestamp,
                        $filePath
                    )
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    __('LLMs.txt generation error for store %1: %2', $storeId, $e->getMessage())
                );
            }
        }
    }
}

