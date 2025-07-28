<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

class GitHubEventsCleaner
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function deleteEventsFiles(\DateTimeInterface $beforeDate): void
    {
        $this->logger->info('Starting GitHub events files cleanUp', [
            'before_date' => $beforeDate->format('Y-m-d H:i:s'),
        ]);

        foreach ($this->filesToDelete($beforeDate) as $filePath) {
            unlink($filePath);
            $this->logger->info('Deleted file', [
                'file_path' => $filePath,
            ]);
        }
    }

    public function filesToDelete(\DateTimeInterface $beforeDate): \Generator
    {
        $extractedFilesPath = $this->parameterBag->get('app.extraction_directory');
        $downloadedFilesPath = $this->parameterBag->get('app.download_directory');

        $jsonFiles = Finder::create()->files()
            ->in($extractedFilesPath)
            ->date('<= ' . $beforeDate->format('Y-m-d H:i:s'))
            ->name('*.json');

        foreach ($jsonFiles as $jsonFile) {
            yield $jsonFile->getRealPath();
        }

        $downloadedFiles = Finder::create()->files()
            ->in($downloadedFilesPath)
            ->date('<= ' . $beforeDate->format('Y-m-d H:i:s'))
            ->name('*.json.gz');

        foreach ($downloadedFiles as $downloadedFile) {
            yield $downloadedFile->getRealPath();
        }
    }
}
