<?php

namespace App\MessageHandler;

use App\Message\ImportGitHubEventsMessage;
use App\Services\EventManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class ImportGitHubEventsMessageHandler
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EventManager $eventManager,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function __invoke(ImportGitHubEventsMessage $message)
    {
        $filePath = sprintf('%s/%s.json.gz', $this->parameterBag->get('app.download_directory'), $message->getDateHour());
        $extractionFilePath = sprintf('%s/%s.json', $this->parameterBag->get('app.extraction_directory'), $message->getDateHour());
        $this->downloadFile(
            sprintf('https://data.gharchive.org/%s.json.gz', $message->getDateHour()),
            $filePath,
        );
        $this->extractFile($filePath, $extractionFilePath);
        $this->processFile($extractionFilePath);
    }

    private function readLinesFromFile(string $extractionFilePath): \Generator
    {
        $handle = fopen($extractionFilePath, 'rb');

        if (!$handle) {
            throw new \RuntimeException("Could not open the file: {$extractionFilePath}");
        }
        try {
            while (($line = fgets($handle)) !== false) {
                yield trim($line);
            }
        } finally {
            fclose($handle);
        }
    }

    public function processFile(string $extractionFilePath): void
    {
        $lines = $this->readLinesFromFile($extractionFilePath);
        foreach ($lines as $line) {
            dump(memory_get_peak_usage(true) / 1024 / 1024);
            // todo: use symfony deserializer to deserialize the line into an Event object
            $array = json_decode($line, true);
            $this->eventManager->saveEvent($array);
        }
    }

    public function extractFile(string $filePath, string $outputPath): string
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($outputPath)) {
            return $outputPath;
        }
        if (!$filesystem->exists($filePath)) {
            throw new \RuntimeException("GZ file does not exist: $filePath");
        }

        if (!str_ends_with(strtolower($filePath), '.gz')) {
            throw new \RuntimeException("Not a .gz file: $filePath");
        }
        try {
            $gzHandle = gzopen($filePath, 'rb');
            if (false === $gzHandle) {
                throw new \RuntimeException('Failed to open GZ file');
            }
            $filesystem->mkdir(dirname($outputPath));
            $outputHandle = fopen($outputPath, 'wb');
            if (false === $outputHandle) {
                throw new \RuntimeException('Failed to create output file');
            }

            while (!gzeof($gzHandle)) {
                $chunk = gzread($gzHandle, 65536); // 64KB chunks
                if (false === $chunk || false === fwrite($outputHandle, $chunk)) {
                    throw new \RuntimeException('Decompression failed');
                }
            }

            return $outputPath;
        } catch (\Throwable $e) {
            if ($filesystem->exists($outputPath)) {
                $filesystem->remove($outputPath);
            }
            throw new \RuntimeException('GZ extraction failed: ' . $e->getMessage());
        } finally {
            if (isset($gzHandle) && is_resource($gzHandle)) {
                gzclose($gzHandle);
            }
            if (isset($outputHandle) && is_resource($outputHandle)) {
                fclose($outputHandle);
            }
        }
    }

    public function downloadFile(string $url, string $targetDirectory): int
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($targetDirectory)) {
            return 0;
        }
        $response = $this->httpClient->request('GET', $url);

        $filesystem->mkdir(dirname($targetDirectory));

        $fileHandler = fopen($targetDirectory, 'wb');
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);

        return 1;
    }
}
