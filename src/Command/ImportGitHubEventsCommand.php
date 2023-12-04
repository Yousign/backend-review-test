<?php

declare(strict_types=1);

namespace App\Command;

use App\Action\GithubEventHandler;
use App\Application\DateRangeParser;
use App\Infrastructure\GithubEvent\GithubEventArchiveDownloader;
use App\Infrastructure\GithubEvent\GithubEventArchiveReader;
use App\Infrastructure\GithubEvent\UriBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
#[AsCommand(name: 'app:import-github-events')]
final class ImportGitHubEventsCommand extends Command
{
    public function __construct(
        private readonly GithubEventHandler $eventHandler
    )
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events')
            ->addArgument('date', InputArgument::REQUIRED, 'The date and hour for the events to import')
        ->setHelp(
            'Allowed date formats are: Y-m-d-G | Y-m-d-{G..G} | Y-m-{d..d}-{G..G}' . PHP_EOL . PHP_EOL .
            'Ex.:' . PHP_EOL .
            "\tY-m-d-G\t\t\t 2011-02-12-10" . PHP_EOL .
            "\tY-m-d-{G..G}\t\t 2011-02-12-{0..23}" . PHP_EOL .
            "\tY-m-{d..d}-{G..G}\t 2011-02-{12..13}-{0..23}" . PHP_EOL
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Let's rock !
        // It's up to you now
        $io = new SymfonyStyle($input, $output);

        $dateArgument = $input->getArgument('date');
        $this->eventHandler->import($dateArgument);

        return Command::SUCCESS;
    }
}
