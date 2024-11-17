<?php

declare(strict_types=1);

namespace App\Downloader;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class GithubEventDownloader implements GithubEventDownloaderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $ghArchiveUrl
    ) {
    }

    public function downloadHourlyEvents(\DateTimeImmutable $period): array
    {
        $url = sprintf(
            '%s/%s.json.gz',
            $this->ghArchiveUrl,
            $period->format('Y-m-d-G')
        );

        try {
            $response = $this->httpClient->request('GET', $url);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'gh_archive_');
            if ($tmpFile === false) {
                return [];
            }

            $tmpHandle = fopen($tmpFile, 'wb');
            if ($tmpHandle === false) {
                unlink($tmpFile);
                return [];
            }

            foreach ($this->httpClient->stream($response) as $chunk) {
                fwrite($tmpHandle, $chunk->getContent());
            }
            fclose($tmpHandle);

            $gz = gzopen($tmpFile, 'rb');
            if ($gz === false) {
                unlink($tmpFile);
                return [];
            }

            $events = [];

            while (!gzeof($gz)) {
                $line = gzgets($gz);
                if ($line === false) {
                    continue;
                }

                $eventData = json_decode($line, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($eventData)) {
                    $event = $this->tryCreateEvent($eventData);
                    if ($event !== null) {
                        $events[] = $event;
                    }
                }
            }

            gzclose($gz);
            unlink($tmpFile);

            return $events;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            if (isset($tmpFile) && file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            return [];
        }
    }

    private function tryCreateEvent(array $event): ?Event
    {
        try {
            if (!isset($event['type']) || !isset(EventType::$eventTypeMapping[$event['type']])) {
                return null;
            }

            if (!isset($event['id'], $event['actor'], $event['repo'], $event['created_at'])) {
                return null;
            }

            return new Event(
                (int) $event['id'],
                EventType::$eventTypeMapping[$event['type']],
                Actor::fromArray($event['actor']),
                Repo::fromArray($event['repo']),
                $event['payload'] ?? [],
                new \DateTimeImmutable($event['created_at']),
                null
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
