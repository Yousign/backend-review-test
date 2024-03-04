<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events'
)]
class ImportGitHubEventsCommand extends Command
{
    private const MIN_CHUNK_SIZE = 500;
    private const MAX_CHUNK_SIZE = 5000;
    private const DEFAULT_CHUNK_SIZE = 2500;
    private const GH_ARCHIVE_ENDPOINT = 'https://data.gharchive.org/';
    private OutputInterface $output;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct('app:import-github-events');
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Import GitHub events at a given date time.')
            ->addArgument(
                'date',
                InputArgument::REQUIRED,
                'An ISO 8601 compliant date and time such as 2015-01-01T15'
            )
            ->addOption(
                'force-download',
                'f',
                InputOption::VALUE_NONE,
                'Bypass the cache and force download'
            )
            ->addOption(
                'chunk-size',
                'c',
                InputOption::VALUE_REQUIRED,
                'Allows to tweak chunks size'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $currentLine = 0;
        $eventsBuffer = [];
        $progressBar = new ProgressBar($output->section());

        try {
            $date = new \DateTimeImmutable($input->getArgument('date'));
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        if ((int)$date->format('Y') < 2015) {
            $output->writeln("<info>Archives before 2015 are not supported yet!</info>");
            return Command::SUCCESS;
        }

        $filename = $date->format('Y-m-d-G') . '.json.gz';
        $filepath = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $downloadUrl = self::GH_ARCHIVE_ENDPOINT . $filename;

        $chunkSize = (int)$input->getOption('chunk-size');
        if ($chunkSize < self::MIN_CHUNK_SIZE || $chunkSize > self::MAX_CHUNK_SIZE) {
            $chunkSize = self::DEFAULT_CHUNK_SIZE;
        }

        if (!\file_exists($filepath) || $input->getOption('force-download')) {
            $output->writeln("<comment>Downloading archive from $downloadUrl</comment>");

            if (!$this->downloadArchive($filepath, $downloadUrl)) {
                return Command::FAILURE;
            }

            $output->writeln("<info>Done! File has been saved at $filepath.</info>");
        } else {
            $output->writeln("<info>Archive $filename found in cache: Download skipped.</info>");
        }

        $totalArchiveEvents = $this->countArchiveEvents($filepath);
        $progressBar->setMaxSteps($totalArchiveEvents);
        $handle = \gzopen($filepath, 'r');

        $output->writeln("<comment>GitHub events extract in progress. Please wait!</comment>");

        while($json = \gzgets($handle)) {
            $eventsBuffer[] = \json_decode($json, true);
            $progressBar->setProgress(++$currentLine);

            if (\count($eventsBuffer) % $chunkSize === 0) {
                if (!$this->saveInBulk($eventsBuffer)) {
                    return Command::FAILURE;
                }
                $eventsBuffer = [];
            }
        }

        if (!$this->saveInBulk($eventsBuffer)) {
            return Command::FAILURE;
        }

        \gzclose($handle);

        $totalTime = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2);
        $memPeakUsage = round(memory_get_peak_usage() / pow(1024, 2), 2);

        $output->writeln([
            "<info>Congratulations! $totalArchiveEvents GitHub events have been extracted.</info>",
            "Elapsed Time: $totalTime s / Memory Usage: $memPeakUsage MB"
        ]);

        return Command::SUCCESS;
    }

    private function downloadArchive(string $filepath, string $downloadUrl): bool
    {
        $progressBar = new ProgressBar($this->output->section());

        try {
            $response = $this->client->request('GET', $downloadUrl, [
                'on_progress' => function (int $dlNow, int $dlSize) use ($progressBar): void {
                    $progressBar->setMaxSteps($dlSize);
                    $progressBar->setProgress($dlNow);
                }
            ]);

            $fileHandler = \fopen($filepath, 'w+');

            foreach ($this->client->stream($response) as $chunk) {
                \fwrite($fileHandler, $chunk->getContent());
            }
        } catch (
        TransportExceptionInterface |
        ClientExceptionInterface |
        RedirectionExceptionInterface |
        ServerExceptionInterface $e
        ) {
            $progressBar->clear();
            \unlink($filepath);
            $this->output->writeln("<error>{$e->getMessage()}</error>");
            return false;
        }

        $progressBar->clear();
        return true;
    }

    private function countArchiveEvents(string $filepath): int
    {
        $totalEvents = 0;
        $progressIndicator = new ProgressIndicator($this->output);
        $progressIndicator->start('Found 0 events');
        $handle = \gzopen($filepath, 'r');

        while(!\feof($handle)) {
            $progressIndicator->advance();
            \gzgets($handle);
            $totalEvents++;
            $progressIndicator->setMessage("Found $totalEvents events");
        }

        \gzclose($handle);

        $progressIndicator->finish("Found $totalEvents events. Now processing:");

        return $totalEvents;
    }

    /**
     * @throws Exception
     */
    private function saveActorsInBulk(array $events): void
    {
        $actorsIds = \array_unique(\array_map(fn(array $event) => $event['actor']['id'], $events));
        $idsPlaceholder = \implode(',', $actorsIds);

        $actorsInDatabase = $this
            ->entityManager
            ->getConnection()
            ->prepare("SELECT id FROM actor WHERE id IN ($idsPlaceholder)")
            ->executeQuery()
            ->fetchFirstColumn()
        ;

        $actorsIds = \array_diff($actorsIds, $actorsInDatabase);

        $actors = [];

        while($event = \current($events)) {
            if (\in_array($event['actor']['id'], $actorsIds)) {
                $actors[] = $event['actor'];
                $actorsIds = \array_diff($actorsIds, [$event['actor']['id']]);
            }
            \next($events);
        }

        if (\count($actors)) {
            $placeholders = \implode(',', \array_fill(0, \count($actors), '(?,?,?,?)'));

            $stmt = $this
                ->entityManager
                ->getConnection()
                ->prepare("INSERT INTO actor (id, login, url, avatar_url) VALUES $placeholders")
            ;

            while($actor = \current($actors)) {
                $key = \key($actors);
                $start = $key * 4 + 1;
                $stmt->bindValue($start, $actor['id']);
                $stmt->bindValue($start+1, $actor['login']);
                $stmt->bindValue($start+2, $actor['url']);
                $stmt->bindValue($start+3, $actor['avatar_url']);
                \next($actors);
            }

            $stmt->executeStatement();
        }
    }

    /**
     * @throws Exception
     */
    private function saveReposInBulk(array $events): void
    {
        $repoIds = \array_unique(\array_map(fn($event) => $event['repo']['id'], $events));
        $idsPlaceholder = \implode(',', $repoIds);

        $reposInDatabase = $this
            ->entityManager
            ->getConnection()
            ->prepare("SELECT id FROM repo WHERE id IN ($idsPlaceholder)")
            ->executeQuery()
            ->fetchFirstColumn()
        ;

        $repoIds = \array_diff($repoIds, $reposInDatabase);

        $repos = [];

        while($event = \current($events)) {
            if (\in_array($event['repo']['id'], $repoIds)) {
                $repos[] = $event['repo'];
                $repoIds = \array_diff($repoIds, [$event['repo']['id']]);
            }
            \next($events);
        }

        if (\count($repos)) {
            $placeholders = \implode(',', \array_fill(0, \count($repos), '(?,?,?)'));

            $stmt = $this
                ->entityManager
                ->getConnection()
                ->prepare("INSERT INTO repo (id, name, url) VALUES $placeholders")
            ;

            while($repo = \current($repos)) {
                $key = \key($repos);
                $start = $key * 3 + 1;
                $stmt->bindValue($start, $repo['id']);
                $stmt->bindValue($start+1, $repo['name']);
                $stmt->bindValue($start+2, $repo['url']);
                \next($repos);
            }

            $stmt->executeStatement();
        }
    }

    /**
     * @throws Exception
     */
    private function saveEventsInBulk(array $events): void
    {
        $eventsIds = \array_unique(\array_map(fn($event) => $event['id'], $events));
        $idsPlaceholder = \implode(',', $eventsIds);

        $eventsInDatabase = $this
            ->entityManager
            ->getConnection()
            ->prepare("SELECT id FROM event WHERE id IN ($idsPlaceholder)")
            ->executeQuery()
            ->fetchFirstColumn()
        ;

        $eventsIds = \array_diff($eventsIds, $eventsInDatabase);

        $eventsToSave = [];

        while($event = \current($events)) {
            if (\in_array($event['id'], $eventsIds)) {
                $eventsToSave[] = $event;
                $eventsIds = \array_diff($eventsIds, [$event['id']]);
            }
            \next($events);
        }

        if (\count($eventsToSave)) {
            $placeholders = \implode(',', \array_fill(0, \count($eventsToSave), '(?,?,?,?,?,?,?,?)'));

            $stmt = $this
                ->entityManager
                ->getConnection()
                ->prepare("INSERT INTO event (id, actor_id, repo_id, type, count, payload, created_at, comment) VALUES $placeholders")
            ;

            while($event = \current($eventsToSave)) {
                $key = \key($eventsToSave);
                $start = $key * 8 + 1;
                $stmt->bindValue($start, $event['id']);
                $stmt->bindValue($start+1, $event['actor']['id']);
                $stmt->bindValue($start+2, $event['repo']['id']);
                $stmt->bindValue($start+3, $event['type']);
                $stmt->bindValue($start+4, $event['payload']['size'] ?? 1);
                $stmt->bindValue($start+5, \json_encode($event['payload']));
                $stmt->bindValue($start+6, $event['created_at']);
                $stmt->bindValue($start+7, $event['comment'] ?? NULL);
                \next($eventsToSave);
            }

            $stmt->executeStatement();
        }
    }

    private function saveInBulk(array $events): bool
    {
        try {
            $this->saveActorsInBulk($events);
            $this->saveReposInBulk($events);
            $this->saveEventsInBulk($events);
        } catch (Exception $e) {
            $this->output->writeln("<error>{$e->getMessage()}</error>");

            return false;
        }

        return true;
    }
}
