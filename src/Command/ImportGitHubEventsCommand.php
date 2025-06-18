<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\GitHubEventImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GitHub events from GH Archive'
)]
class ImportGitHubEventsCommand extends Command
{

    public function __construct(
        private readonly GitHubEventImporter $importer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'The URL of the .json.gz file from GH Archive');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        if(!is_string($url)) {
            $output->writeln("<error>❌ Invalid URL provided.</error>");
            return Command::FAILURE;
        }
        $output->writeln("Starting import from: <info>$url</info>");

        try {
            $count = $this->importer->importFromUrl($url);
            $output->writeln("✅ <info>$count</info> events imported successfully.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln("<error>❌ Error during import: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
