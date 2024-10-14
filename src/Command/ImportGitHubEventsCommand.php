<?php

declare(strict_types=1);

namespace App\Command;

use App\Importer\GHArchiveImporterInterface;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events',
)]
final class ImportGitHubEventsCommand extends Command
{
    public function __construct(
        private readonly GHArchiveImporterInterface $importer,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'from',
                mode: InputArgument::REQUIRED,
                description: 'Import events *from* this date (expected format: yyyy-mm-dd-H)',
            )
            ->addArgument(
                name: 'to',
                mode: InputArgument::OPTIONAL,
                description: 'Import events *up to* this date (expected format: yyyy-mm-dd-H). If not set, it will only import GH events for the ',
            )
            ->setHelp(<<<TXT
This command dispatches messages to import GH events for a given period of time.

Dates must follow this format: yyyy-mm-dd-H.
Hours follows the 24-hour format.

For example:
    * `app:import-github-events 2015-01-01-0 2015-01-01-23` imports events that occurred on the 1st of January 2015 (all day)
    * `app:import-github-events 2015-02-14-15` imports all events that occurred between 2015-02-14 15:00 PM to 2015-02-14 15:59 PM
    * `app:import-github-events 2015-10-12-01 2015-10-12-13` imports all events that occurred between 2015-10-12 01:00 AM to 2015-10-12 PM:00 AM
TXT
)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $from = DateTimeImmutable::createFromFormat('Y-m-d-H', $input->getArgument('from'));
        if (false === $from) {
            $io->error(sprintf(
                'Wrong date format for the first argument. Make sure the format is "Y-m-d-H". Value received is "%s".',
                $input->getArgument('from'),
            ));

            return self::FAILURE;
        }

        $to = $input->getArgument('to') === null ? null : DateTimeImmutable::createFromFormat('Y-m-d-H', $input->getArgument('to'));
        if (false === $to) {
            $io->error(sprintf(
                'Wrong date format for the second argument. Make sure the format is "Y-m-d-H". Value received is "%s".',
                $input->getArgument('to'),
            ));

            return self::FAILURE;
        }

        $startTime = time();
        $eventsProcessedCount = $this->importer->import($from, $to);
        $endTime = time();

        $io->section(sprintf(
            'Dispatched messages to import %d events in %d seconds. Memory peak usage: %d MB.',
            $eventsProcessedCount,
            $endTime - $startTime,
            memory_get_peak_usage(true) / 1024 / 1024,
        ));

        return self::SUCCESS;
    }
}
