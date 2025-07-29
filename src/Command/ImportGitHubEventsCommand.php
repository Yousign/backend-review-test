<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
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
            ->addArgument(
                'year',
                InputArgument::REQUIRED,
                'Year to import (e.g., 2015)'
            )
            ->addOption(
                'month',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Month to import (1-12). If not provided, imports entire year'
            )
            ->addOption(
                'day',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Day to import (1-31). If not provided, imports entire month'
            )
            ->addOption(
                'hour',
                'H',
                InputOption::VALUE_OPTIONAL,
                'Hour to import (0-23). If not provided, imports entire day'
            )
            ->addOption(
                'keyword',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Keyword to filter events (optional)'
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
        
        $year = (int) $input->getArgument('year');
        $month = $input->getOption('month') ? (int) $input->getOption('month') : null;
        $day = $input->getOption('day') ? (int) $input->getOption('day') : null;
        $hour = $input->getOption('hour') ? (int) $input->getOption('hour') : null;
        $keyword = $input->getOption('keyword');
        $dryRun = $input->getOption('dry-run');

        // Validate parameters
        $validationError = $this->validateParameters($year, $month, $day, $hour);
        if ($validationError !== null) {
            $io->error($validationError);
            return Command::FAILURE;
        }

        // Display what will be imported
        $scope = $this->getImportScope($year, $month, $day, $hour);
        $io->info(sprintf('Importing GitHub events for: %s', $scope));
        
        if ($keyword) {
            $io->info(sprintf('Filtering by keyword: %s', $keyword));
        }

        try {
            // Convert null keyword to empty string
            $keywordString = $keyword ?? '';
            
            if ($dryRun) {
                $importedCount = $this->gitHubArchiveService->dryRunImportFromGHArchive($io, $year, $month, $day, $hour, $keywordString);
                $io->success(sprintf('Would import %d events (dry run)', $importedCount));
            } else {
                $importedCount = $this->gitHubArchiveService->importEventsFromGHArchive($year, $month, $day, $hour, $keywordString);
                $io->success(sprintf('Successfully imported %d events', $importedCount));
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error importing events: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function getImportScope(int $year, ?int $month, ?int $day, ?int $hour): string
    {
        if ($hour !== null) {
            return sprintf('%d-%02d-%02d at %02d:00 UTC', $year, $month, $day, $hour);
        } elseif ($day !== null) {
            return sprintf('%d-%02d-%02d (entire day)', $year, $month, $day);
        } elseif ($month !== null) {
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            return sprintf('%s %d (entire month)', $monthName, $year);
        } else {
            return sprintf('%d (entire year)', $year);
        }
    }

    /**
     * Validate the command parameters
     * 
     * @param int $year
     * @param int|null $month
     * @param int|null $day
     * @param int|null $hour
     * @return string|null Returns error message if validation fails, null if valid
     */
    private function validateParameters(int $year, ?int $month, ?int $day, ?int $hour): ?string
    {
        // Validate year
        if ($year > date('Y')) {
            return 'Invalid year. GitHub Archive data can only be imported for the current year or earlier.';
        }

        // Validate month
        if ($month !== null && ($month < 1 || $month > 12)) {
            return 'Invalid month. Must be between 1 and 12';
        }

        // Validate day
        if ($day !== null) {
            if ($day < 1 || $day > 31) {
                return 'Invalid day. Must be between 1 and 31';
            }
            
            // Check if day exists in the given month/year
            if ($month !== null) {
                $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
                if ($day > $daysInMonth) {
                    return sprintf('Invalid day. Month %d in year %d has only %d days', $month, $year, $daysInMonth);
                }
            }
        }

        // Validate hour
        if ($hour !== null && ($hour < 0 || $hour > 23)) {
            return 'Invalid hour. Must be between 0 and 23';
        }

        // Validate logical dependencies
        if ($day !== null && $month === null) {
            return 'Cannot specify day without month';
        }

        if ($hour !== null && $day === null) {
            return 'Cannot specify hour without day';
        }

        return null; // All validations passed
    }
}