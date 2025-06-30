<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function fclose;
use function file_exists;
use function fopen;
use function fwrite;

class ArchiveDownloader
{
    private const string GH_ARCHIVE_ENDPOINT = 'https://data.gharchive.org';

    private ?string $testFile = null;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function download(string $filepath, string $date): bool
    {
        if ($this->testFile !== null) {
            return copy($this->testFile, $filepath);
        }

        try {
            $url = sprintf('%s/%s.json.gz', self::GH_ARCHIVE_ENDPOINT, $date);

            $response = $this->client->request('GET', $url);
            $fileHandler = fopen($filepath, 'w+');

            foreach ($this->client->stream($response) as $chunk) {
                fwrite($fileHandler, $chunk->getContent());
            }

            fclose($fileHandler);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());

            if (file_exists($filepath)) {
                unlink($filepath);
            }

            return false;
        }

        return true;
    }

    public function setTestFile(string $filepath): void
    {
        $this->testFile = $filepath;
    }
}
