<?php
declare(strict_types=1);

namespace Klever\BrandSync\Console\Command;

use Klever\BrandSync\Model\BrandPatternSync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    private BrandPatternSync $sync;

    public function __construct(BrandPatternSync $sync)
    {
        $this->sync = $sync;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('klever:brand:sync')
            ->setDescription('Sync missing brands and patterns from product data into mgs_brand and mgs_brand_patternmanagement tables');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting brand & pattern sync...</info>');

        $result = $this->sync->execute();

        $output->writeln('');
        $output->writeln('<info>=== Sync Complete ===</info>');
        $output->writeln("Brands created:   <comment>{$result['brands_created']}</comment>");
        $output->writeln("Brands skipped:   {$result['brands_skipped']}");
        $output->writeln("Patterns created: <comment>{$result['patterns_created']}</comment>");
        $output->writeln("Patterns skipped: {$result['patterns_skipped']}");
        $output->writeln("Meta updated:     <comment>{$result['meta_updated']}</comment>");
        $output->writeln("Tabs updated:     <comment>{$result['tabs_updated']}</comment>");
        $output->writeln("Pattern meta:     <comment>{$result['pattern_meta_updated']}</comment>");

        if (!empty($result['errors'])) {
            $output->writeln('');
            $output->writeln('<error>Errors:</error>');
            foreach ($result['errors'] as $err) {
                $output->writeln("  - {$err}");
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
