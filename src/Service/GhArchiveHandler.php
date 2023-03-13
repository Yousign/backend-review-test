<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Provides
 */
class GhArchiveHandler
{
    private EntityManagerInterface  $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Process given archive and stores it to db
     *
     * @param string $archive
     * @return void
     * @throws \Exception
     */
    public function processArchive(string $archive, ProgressBar $progressBar): void
    {
        $count = 0;
        // Open the compressed file for reading
        $file_handle = gzopen($archive, 'r');
        while (!gzeof($file_handle)) {
            $count++;

            // Since we throw error on decode we have to clean string and replace NULL values
            try {
                $string = gzgets($file_handle);
                $line = json_decode($string, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \Exception('Error on following string : ' . $string . ' | Detail : ' . $e->getMessage());
            }

            // Checking that retrieved event type is within choices
            $type = array_filter(EventType::getChoices(), function ($choice, $value) use ($line) {
                return str_contains($line['type'], $value) !== false;
            }, ARRAY_FILTER_USE_BOTH);

            // If type is correct
            if (count($type) > 0) {
                // Counting line to manage memory usage
                $count++;
                $this->processLine($line, $type);
                // Handling memory usage to prevent exceeds memory limit
                if ($count % 500 === 0) {
                    $progressBar->advance();
                    $this->cleanMemoryUsage($count);
                }
            }
        }
        gzclose($file_handle);
    }

    /**
     * @param array $line
     * @param array $type
     * @return void
     * @throws \JsonException
     */
    private function processLine(array $line, array $type): void
    {
        // Preventing duplicates error
        $actor = $this->manager->getRepository(Actor::class)->find(+$line['actor']['id']);
        if (empty($actor)) {
            $actor = new Actor(+$line['actor']['id'], $line['actor']['login'],$line['actor']['url'], $line['actor']['avatar_url']);
        }

        // Preventing duplicates error
        $repo = $this->manager->getRepository(Repo::class)->find(+$line['repo']['id']);
        if (empty($repo)) {
            $repo = new Repo(+$line['repo']['id'], $line['repo']['name'], $line['repo']['url']);
        }

        $event = new Event(
            +$line['id'],
            key(array_flip($type)),
            $actor,
            $repo,
            $line['payload'],
            DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $line['created_at']),
            ''
        );

        // Saving to db
        $this->manager->persist($event);
    }

    /**
     * @param int $count
     * @return void
     */
    private function cleanMemoryUsage(int $count): void
    {
        $this->manager->flush();
        $this->manager->clear();
        gc_collect_cycles();
    }
}
