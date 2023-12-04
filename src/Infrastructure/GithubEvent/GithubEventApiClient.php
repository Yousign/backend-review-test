<?php

namespace App\Infrastructure\GithubEvent;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GithubEventApiClient implements GithubEventApiClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly Filesystem $filesystem
    ){
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function retrieve(string $url): iterable
    {
        try {
            $basename = basename($url);
            $basedir = realpath(__DIR__. '/../../../var/filesystem') . '/';
            $filename = $basedir . $basename;
            if ($this->filesystem->exists($filename)) {
                yield $filename;
                return;
            }
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Accept-Encoding' => 'gzip',
                    ]
                ]
            );
            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new \RuntimeException('Failed to fetch Github event archive');
            }
            // @TODO: change this for an EFS mount if possible !!!
            $fileHandler = fopen($filename, 'w');
            foreach ($this->client->stream($response) as $chunk) {
                fwrite($fileHandler, $chunk->getContent());
            }
            fclose($fileHandler);
            yield $filename;
        } catch (Exception $e) {
            $this->logger->error('Error: ' . $e->getMessage());

            throw $e;
        }
    }
}
