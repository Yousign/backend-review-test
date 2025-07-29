<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Utils\DateUtils;
use App\Service\Interfaces\GithubArchiveInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
class ImportGitHubEventsCommand extends Command
{
    protected static $defaultName = 'app:import-github-events';

    private GithubArchiveInterface $gitHubArchiveService;

    public function __construct(GithubArchiveInterface $gitHubArchiveService)
    {
        $this->gitHubArchiveService = $gitHubArchiveService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GitHub events from GH Archive')
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_REQUIRED,
                'Date to import (YYYY-MM-DD format)',
                date('Y-m-d')
            )
            ->addOption(
                'hour',
                null,
                InputOption::VALUE_REQUIRED,
                'Hour to import (0-23)',
                '0'
            )
            ->addOption(
                'keyword',
                'k',
                InputOption::VALUE_REQUIRED,
                'Keyword to filter events (optional)',
                null
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be imported without saving to database'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $date = $input->getOption('date');
        $hour = $input->getOption('hour');
        $keyword = $input->getOption('keyword');
        $dryRun = $input->getOption('dry-run');

        // Validate date format
        if (!DateUtils::validateDate($date)) {
            $io->error('Invalid date format. Use YYYY-MM-DD');
            return Command::FAILURE;
        }
        
        // Validate hour
        if (!DateUtils::validateHour((int) $hour)) {
            $io->error('Invalid hour. Must be between 0 and 23');
            return Command::FAILURE;
        }

        $io->info(sprintf('Importing GitHub events for %s hour %s', $date, $hour));
        if ($keyword) {
            $io->info(sprintf('Filtering by keyword: %s', $keyword));
        }

        try {
            if ($dryRun) {
                $importedCount = $this->gitHubArchiveService->dryRunImportFromGHArchive($date, (int) $hour, $keyword);
                $io->success(sprintf('Would import %d events (dry run)', $importedCount));
            } else {
                $importedCount = $this->gitHubArchiveService->importEventsFromGHArchive($date, (int) $hour, $keyword);
                $io->success(sprintf('Successfully imported %d events', $importedCount));
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error importing events: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
