<?php

declare(strict_types=1);

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class GHArchiveClient
{
    private const URL = 'https://data.gharchive.org/%s-%s.json.gz';
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function downloadEvents(string $date, string $hour, array $options = []): string
    {
        $response = $this->httpClient->request(Request::METHOD_GET, \sprintf(self::URL, $date, $hour), $options);

        $destination = \sprintf('/tmp/%s-%s.json.gz', $date, $hour);
        $fileHandle = fopen($destination, 'w');
        foreach ($this->httpClient->stream($response) as $chunk) {
            if ($chunk->isTimeout()) {
                continue;
            }

            if ($chunk->isLast()) {
                break;
            }

            fwrite($fileHandle, $chunk->getContent());
        }

        fclose($fileHandle);

        return $destination;
    }
}
