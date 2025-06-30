<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ArchiveDownloader;
use App\Service\Persister\ActorPersister;
use App\Service\Persister\EventPersister;
use App\Service\Persister\RepoPersister;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function count;

#[AsCommand(name: 'app:import-github-events', description: 'Import GH events')]
class ImportGitHubEventsCommand extends Command
{
    private const int CHUNK_SIZE = 1000;

    private OutputInterface $output;

    public function __construct(
        private readonly ActorPersister $actorPersister,
        private readonly ArchiveDownloader $archiveDownloader,
        private readonly EventPersister $eventPersister,
        private readonly RepoPersister $repoPersister
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'date',
                mode: InputArgument::REQUIRED,
                description: 'Date and hour in format YYYY-MM-DD-HH (e.g. 2015-01-01-15)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $filename = sprintf('%s.json.gz', uniqid());
        $filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $this->output->writeln("<info>Downloading archive...</info>");

        $this->archiveDownloader->download(
            filepath: $filepath,
            date: $input->getArgument('date')
        );

        $handle = gzopen($filepath, 'r');
        if (!$handle) {
            return Command::FAILURE;
        }

        $chunkSize = self::CHUNK_SIZE;

        while ($json = gzgets($handle)) {
            $eventsBuffer[] = json_decode($json, true);

            if (count($eventsBuffer) % $chunkSize === 0) {
                if (!$this->persistEventsChunk($eventsBuffer)) {
                    return Command::FAILURE;
                }
                $eventsBuffer = [];
            }
        }

        if (!empty($eventsBuffer)) {
            if (!$this->persistEventsChunk($eventsBuffer)) {
                return Command::FAILURE;
            }
        }

        gzclose($handle);

        return Command::SUCCESS;
    }

    private function persistEventsChunk(array $events): bool
    {
        try {
            $numberOfPersistedActors = $this->actorPersister->persistFromEvents($events);
            $numberOfPersistedRepos = $this->repoPersister->persistFromEvents($events);
            $numberOfPersistedEvents = $this->eventPersister->persistFromEvents($events);
        } catch (Exception $e) {
            $this->output->writeln("<error>{$e->getMessage()}</error>");

            return false;
        }

        $this->output->writeln(
            sprintf(
                "<info>Persisted %d actors, %d repos and %d events</info>",
                $numberOfPersistedActors,
                $numberOfPersistedRepos,
                $numberOfPersistedEvents
            )
        );

        return true;
    }
}
