<?php

declare(strict_types=1);

namespace App\Command;

use App\Client\GHArchiveClient;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Parser\GzipJsonParser;
use App\Repository\DbalWriteObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ImportGitHubEventsCommand extends Command
{
    private const BATCH_SIZE = 1000;

    public function __construct(
        private GHArchiveClient $client,
        private GzipJsonParser $parser,
        private SerializerInterface $serializer,
        private DbalWriteObjectManager $objectManager,
    ) {
        parent::__construct();
    }

    protected static $defaultName = 'app:import-github-events';

    protected function configure(): void
    {
        $now = new \DateTimeImmutable('-1 hour');

        $this
            ->setDescription('Import GH events')
            ->setHelp('This command allows you to import GH events')
            ->addArgument('date', InputArgument::OPTIONAL, 'Date of the events to import', $now->format('Y-m-d'))
            ->addArgument('hour', InputArgument::OPTIONAL, 'Hour of the events to import', $now->format('G'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');
        $hour = $input->getArgument('hour');

        $this->validate($date, $hour);

        $output->write(sprintf('Importing %s-%s.json.gz : Download...    ', $date, $hour));

        $eventsFile = $this->client->downloadEvents($date, $hour);

        $output->write(sprintf("\rImporting %s-%s.json.gz : Processing...   ", $date, $hour));

        $rows = $this->parser->parse($eventsFile);

        $n = 0;
        foreach ($rows as $row) {
            try {
                try {
                    $inserts[] = $this->serializer->deserialize($row, Event::class, 'json');
                } catch (\Throwable $e) {
                    $output->writeln(sprintf('Error processing event: %s', $e->getMessage()), OutputInterface::VERBOSITY_VERY_VERBOSE);
                }
                $n++;
                if ($n % self::BATCH_SIZE === 0) {
                    $repos = [];
                    $actors = [];
                    $events = [];
                    foreach ($inserts as $insert) {
                        $repos[$insert->getRepo()->getId()] = [$insert->getRepo()->getId(), $insert->getRepo()->getName(), $insert->getRepo()->getUrl()];
                        $actors[$insert->getActor()->getId()] = [$insert->getActor()->getId(), $insert->getActor()->getLogin(), $insert->getActor()->getUrl(), $insert->getActor()->getAvatarUrl()];
                        $events[$insert->getId()] = [$insert->getId(), $insert->getType(), $insert->getCount(), $insert->getRepo()->getId(), $insert->getActor()->getId(), json_encode($insert->getPayload()), $insert->getCreateAt()->format('c'), $insert->getComment()];
                    }

                    $this->objectManager->upsert(Repo::class, $repos);
                    $this->objectManager->upsert(Actor::class, $actors);
                    $this->objectManager->upsert(Event::class, $events);

                    unset($inserts, $repos, $actors, $events);

                    $output->writeln('Flushed after processing ' . $n . ' events.', OutputInterface::VERBOSITY_VERBOSE);
                }
            } catch (\Throwable $e) {
                $output->writeln(sprintf('Error processing event: %s', $e->getMessage()), OutputInterface::VERBOSITY_VERBOSE);
            }
        }


        $output->write(sprintf("\rImporting %s-%s.json.gz : Done!            \n", $date, $hour));

        $output->writeln($n . ' events processed.');

        return Command::SUCCESS;
    }

    private function validate(string $date, string $hour): void
    {
        if (!$this->isValidDate($date)) {
            throw new \InvalidArgumentException('Invalid date format. Please use Y-m-d format.');
        }

        if (!$this->isValidHour($hour)) {
            throw new \InvalidArgumentException('Invalid hour format. Please provide an hour between 0 and 23.');
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidHour(string $hour): bool
    {
        return is_numeric($hour) && (int) $hour >= 0 && (int) $hour <= 23;
    }
}
