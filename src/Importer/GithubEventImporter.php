<?php

namespace App\Importer;

use App\Downloader\GithubEventDownloaderInterface;
use App\Repository\WriteEventRepository;
use Psr\Log\LoggerInterface;

class GithubEventImporter
{
    public function __construct(
        private readonly GithubEventDownloaderInterface $downloader,
        private readonly WriteEventRepository $writeEventRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function importerEvents(string $date): void
    {
        $dateTime = new \DateTimeImmutable($date);

        for ($hour = 0; $hour < 24; $hour++) {
            try {
                $period = $dateTime->setTime($hour, 0);
                $hourlyEvents = $this->downloader->downloadHourlyEvents($period);

                if (count($hourlyEvents) === 0) {
                    continue;
                }

                $this->writeEventRepository->saveEvents($hourlyEvents);

                $this->logger->info(sprintf('Importation des événements pour l\'heure %02d terminée.', $hour));
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Erreur lors de l\'importation des événements pour l\'heure %02d : %s', $hour, $e->getMessage()), ['exception' => $e]);
            }
        }
    }
}
