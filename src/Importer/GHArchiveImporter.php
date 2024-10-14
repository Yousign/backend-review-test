<?php

declare(strict_types=1);

namespace App\Importer;

use App\Downloader\GHArchiveDownloader;
use App\Dto\GHArchiveEvent;
use App\Message\GHArchiveEvents;
use App\Reader\GHArchiveReader;
use Symfony\Component\Messenger\MessageBusInterface;

final class GHArchiveImporter implements GHArchiveImporterInterface
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly GHArchiveDownloader $downloader,
        private readonly GHArchiveReader $reader,
        private readonly MessageBusInterface $bus,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function import(\DateTimeImmutable $from, ?\DateTimeImmutable $to = null): int
    {
        $eventsProcessedCount = 0;

        /** @var array<GHArchiveEvent> $events */
        $events = [];
        foreach ($this->downloader->download($from, $to) as $filename) {
            foreach ($this->reader->readAndDelete($filename) as $ghArchiveEvent) {
                $events[] = $ghArchiveEvent;
                $eventsProcessedCount++;

                if (($eventsProcessedCount % self::BATCH_SIZE) === 0) {
                    $this->dispatchBatch($events);
                    $events = [];
                }
            }
        }

        $this->dispatchBatch($events);

        return $eventsProcessedCount;
    }

    /**
     * @param array<GHArchiveEvent> $events
     */
    private function dispatchBatch(array $events): void
    {
        if ([] !== $events) {
            $this->bus->dispatch(new GHArchiveEvents($events));
        }
    }
}
