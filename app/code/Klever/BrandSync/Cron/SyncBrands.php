<?php
declare(strict_types=1);

namespace Klever\BrandSync\Cron;

use Klever\BrandSync\Model\BrandPatternSync;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class SyncBrands
{
    private BrandPatternSync $sync;
    private ScopeConfigInterface $scopeConfig;
    private LoggerInterface $logger;

    public function __construct(
        BrandPatternSync $sync,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->sync = $sync;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $enabled = $this->scopeConfig->isSetFlag('klever_brandsync/general/enable_cron');
        if (!$enabled) {
            return;
        }

        $this->logger->info('BrandSync cron: starting');
        $result = $this->sync->execute();
        $this->logger->info('BrandSync cron: done', $result);
    }
}
