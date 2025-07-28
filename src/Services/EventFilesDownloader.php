<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EventFilesDownloader
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {}

    public function downloadFile(string $url, string $targetDirectory): void
    {
        $this->logger->debug('Starting file downloading', [
            'source_url' => $url,
            'output_path' => $targetDirectory,
        ]);

        if ($this->filesystem->exists($targetDirectory)) {
            return;
        }

        $response = $this->httpClient->request('GET', $url);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException("Failed to download file from: $url");
        }

        $this->filesystem->mkdir(dirname($targetDirectory));
        $fileHandler = fopen($targetDirectory, 'wb');
        if (false === $fileHandler) {
            throw new \RuntimeException("Failed to create output file: $targetDirectory");
        }

        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        $this->logger->debug('File downloaded successfully', [
            'output_path' => $targetDirectory,
        ]);
        fclose($fileHandler);
    }
}
