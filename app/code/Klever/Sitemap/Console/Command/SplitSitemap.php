<?php

namespace Klever\Sitemap\Console\Command;

use Klever\Sitemap\Model\SitemapSplitter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SplitSitemap extends Command
{
    private SitemapSplitter $splitter;

    public function __construct(
        SitemapSplitter $splitter,
        ?string $name = null
    ) {
        $this->splitter = $splitter;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('klever:sitemap:split')
            ->setDescription('Split sitemap.xml into category-wise separate sitemaps with a sitemap index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting sitemap split...</info>');

        try {
            $summary = $this->splitter->execute();

            $output->writeln('');
            $output->writeln('<info>=== Sitemap Split Summary ===</info>');

            $total = $summary['total'] ?? 0;
            unset($summary['total']);

            foreach ($summary as $file => $count) {
                if ($count > 0) {
                    $output->writeln(sprintf('  <comment>%s</comment>: %d URLs', $file, $count));
                }
            }

            $output->writeln('');
            $output->writeln(sprintf('<info>Total: %d URLs split into %d files</info>', $total, count(array_filter($summary))));
            $output->writeln('<info>Sitemap index written to: pub/sitemap.xml</info>');
            $output->writeln('<info>Original backup saved to: pub/sitemap-original.xml</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
