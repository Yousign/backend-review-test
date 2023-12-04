<?php

declare(strict_types=1);

namespace App\Action;

use App\Application\DateRangeParser;
use App\Domain\Entity\Event;
use App\Domain\Repository\WriteEventRepositoryInterface;
use App\Infrastructure\GithubEvent\GithubEventArchiveDownloader;
use App\Infrastructure\GithubEvent\GithubEventArchiveDownloaderInterface;
use App\Infrastructure\GithubEvent\GithubEventArchiveReader;
use App\Infrastructure\GithubEvent\GithubEventArchiveReaderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GithubEventHandler
{
    public function __construct(
        private readonly DateRangeParser               $dateParser,
        private readonly GithubEventArchiveDownloaderInterface  $downloader,
        private readonly GithubEventArchiveReaderInterface      $reader,
        private readonly WriteEventRepositoryInterface $eventRepository,
        private readonly DenormalizerInterface         $denormalizer,
    ) {}

    public function import(string $dateRange): void {
        $datePeriods = $this->dateParser->parse($dateRange);
        foreach ($datePeriods as $datePeriod) {
            $eventFiles = $this->downloader->download($datePeriod);
            foreach ($eventFiles as $eventFile) {
                $data = $this->reader->read($eventFile);
                foreach ($data as $eventData) {
                    $event = $this->denormalizer->denormalize($eventData, Event::class);
                    if (!empty($event)) {
                        $this->eventRepository->batchInsert($event);
                    }
                }
            }
        }
    }
}
