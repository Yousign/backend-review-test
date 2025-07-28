<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ImportGitHubEventsMessage;
use App\Services\EventFilesDownloader;
use App\Services\EventFilesExtractor;
use App\Services\EventFilesReader;
use App\Services\EventManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportGitHubEventsMessageHandler
{
    public function __construct(
        private readonly EventManager $eventManager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly EventFilesDownloader $eventFilesDownloader,
        private readonly EventFilesExtractor $eventFilesExtractor,
        private readonly EventFilesReader $eventFilesReader,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(ImportGitHubEventsMessage $message): void
    {
        $dateHour = $message->getDateHour();
        $this->logger->debug('Starting GitHub events import process ' . $dateHour);

        $filePath = sprintf('%s/%s.json.gz', $this->parameterBag->get('app.download_directory'), $dateHour);
        $extractionFilePath = sprintf('%s/%s.json', $this->parameterBag->get('app.extraction_directory'), $dateHour);

        $this->eventFilesDownloader->downloadFile(
            sprintf('https://data.gharchive.org/%s.json.gz', $dateHour),
            $filePath,
        );

        $this->eventFilesExtractor->extractFile($filePath, $extractionFilePath);

        $this->processFile($extractionFilePath);
        $this->logger->debug('GitHub events import process Finished ' . $dateHour);

    }

    private function processFile(string $extractionFilePath): void
    {
        foreach ($this->eventFilesReader->readLinesFromFile($extractionFilePath) as $line) {
            $array = json_decode($line, true);
            $this->eventManager->saveEvent($array);

        }
    }
}
