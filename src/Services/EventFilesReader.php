<?php

namespace App\Services;

use Psr\Log\LoggerInterface;

class EventFilesReader
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function readLinesFromFile(string $extractionFilePath): \Generator
    {
        $this->logger->info('Reading lines from file', ['file_path' => $extractionFilePath]);
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
}
