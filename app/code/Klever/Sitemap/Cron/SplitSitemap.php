<?php

namespace Klever\Sitemap\Cron;

use Klever\Sitemap\Model\SitemapSplitter;
use Psr\Log\LoggerInterface;

class SplitSitemap
{
    private SitemapSplitter $splitter;
    private LoggerInterface $logger;

    public function __construct(
        SitemapSplitter $splitter,
        LoggerInterface $logger
    ) {
        $this->splitter = $splitter;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $this->splitter->execute();
        } catch (\Exception $e) {
            $this->logger->error('Klever_Sitemap cron error: ' . $e->getMessage());
        }
    }
}
