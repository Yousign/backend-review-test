<?php

declare(strict_types=1);

namespace App\Command;

use App\Importer\GithubEventImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportGitHubEventsCommand extends Command
{
    protected static $defaultName = 'app:import-github-events';

    public function __construct(
        private GithubEventImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GitHub events from GH Archive')
            ->addArgument(
                'date',
                InputArgument::REQUIRED,
                'Date to import (format: YYYY-MM-DD)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTimeImmutable();

        $this->importer->importerEvents($date->format('Y-m-d'));

        $output->writeln('Importation terminée.');

        return Command::SUCCESS;
    }
}
