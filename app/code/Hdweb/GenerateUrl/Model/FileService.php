<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;

class FileService
{
    /**
     * @var WriteInterface
     */
    private $pubDirectory;

    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        private readonly LoggerInterface $logger,
    ) {
        $this->pubDirectory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
    }

    /**
     * Save content to file in pub directory
     *
     * @param string $filename
     * @param string $content
     * @param int|null $storeId
     * @return bool
     */
    public function saveFile(string $filename, string $content, ?int $storeId = null): bool
    {
        try {
            // Ensure filename ends with .txt
            if (substr($filename, -4) !== '.txt') {
                $filename .= '.txt';
            }

            // Write file to pub directory
            $this->pubDirectory->writeFile($filename, $content);

            $this->logger->info(
                __('LLMs.txt file saved successfully: %1 (Store ID: %2)', $filename, $storeId ?? 'default')
            );

            return true;
        } catch (FileSystemException $e) {
            $this->logger->error(
                __('Failed to save LLMs.txt file %1: %2', $filename, $e->getMessage())
            );
            return false;
        }
    }

    /**
     * Get full file path in pub directory
     *
     * @param string $filename
     * @return string
     */
    public function getFilePath(string $filename): string
    {
        if (substr($filename, -4) !== '.txt') {
            $filename .= '.txt';
        }

        return $this->pubDirectory->getAbsolutePath($filename);
    }

    /**
     * Check if file exists
     *
     * @param string $filename
     * @return bool
     */
    public function fileExists(string $filename): bool
    {
        if (substr($filename, -4) !== '.txt') {
            $filename .= '.txt';
        }

        return $this->pubDirectory->isExist($filename);
    }

    /**
     * Delete file from pub directory
     *
     * @param string $filename
     * @return bool
     */
    public function deleteFile(string $filename): bool
    {
        try {
            if (substr($filename, -4) !== '.txt') {
                $filename .= '.txt';
            }

            if ($this->pubDirectory->isExist($filename)) {
                $this->pubDirectory->delete($filename);
                return true;
            }

            return false;
        } catch (FileSystemException $e) {
            $this->logger->error(
                __('Failed to delete LLMs.txt file %1: %2', $filename, $e->getMessage())
            );
            return false;
        }
    }
}

