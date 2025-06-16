<?php

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

readonly class GitHubEventImporter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger,
        private HttpClientInterface    $http
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function importFromUrl(string $url): int
    {
        $response = $this->http->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException("Failed to fetch data: HTTP " . $response->getStatusCode());
        }

        $compressedContent = $response->getContent();

        if (empty($compressedContent)) {
            throw new RuntimeException("❌ Received empty content from GH archive URL");
        }

        $content = gzdecode($compressedContent);
        if (!$content) {
            throw new RuntimeException("❌ Failed to decompress GH archive file");
        }

        $lines = explode("\n", $content);
        $imported = 0;

        foreach ($lines as $line) {
            if ('' === trim($line)) {
                $this->logger->warning("⚠️ Empty line found in data, skipping.");
                continue;
            }

            /** @var array<string, mixed>|null $raw */
            $raw = json_decode($line, true);
            if (!is_array($raw) || !$this->isSupported($raw)) {
                $this->logger->warning("⚠️ Unsupported event data: " . json_encode($raw));
                continue;
            }

            try {
                $actorData = $raw['actor'];
                if (!is_array($actorData) || !isset($actorData['id'], $actorData['login'], $actorData['url'], $actorData['avatar_url'])) {
                    $this->logger->warning("⚠️ Malformed actor data, skipping.");
                    continue;
                }

                $actorId = $actorData['id'];
                if ($actorId <= 0) {
                    $this->logger->warning("⚠️ Invalid actor ID: $actorId, skipping event.");
                    continue;
                }

                $actor = $this->entityManager->getRepository(Actor::class)->find($actorId)
                    ?? Actor::fromArray($actorData);
                $this->entityManager->persist($actor);

                $repoData = $raw['repo'];
                if (!is_array($repoData) || !isset($repoData['id'], $repoData['name'], $repoData['url'])) {
                    $this->logger->warning("⚠️ Malformed repo data, skipping.");
                    continue;
                }

                $repoId = $repoData['id'];
                if ($repoId <= 0) {
                    $this->logger->warning("⚠️ Invalid repo ID: $repoId, skipping event.");
                    continue;
                }

                $repo = $this->entityManager->getRepository(Repo::class)->find($repoId)
                    ?? Repo::fromArray($repoData);
                $this->entityManager->persist($repo);

                $eventId = $raw['id'];
                if ($this->entityManager->getRepository(Event::class)->find($eventId)) {
                    $this->logger->info("ℹ️ Event ID $eventId already exists. Skipping.");
                    continue;
                }

                /** @var array<string, mixed> $payload */
                $payload = is_array($raw['payload'] ?? null) ? $raw['payload'] : [];

                $comment = null;
                if (is_array($payload['comment']) && isset($payload['comment']['body']) && is_string($payload['comment']['body'])) {
                    $comment = $payload['comment']['body'];
                }

                $event = new Event(
                    $eventId,
                    $this->mapEventType($raw['type']),
                    $actor,
                    $repo,
                    $payload,
                    new DateTimeImmutable($raw['created_at']),
                    $comment
                );

                $this->entityManager->persist($event);
                $imported++;

                if ($imported % 50 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }

            } catch (Throwable $e) {
                $this->logger->error('❌ Failed to import event ID ' . ($raw['id'] ?? 'UNKNOWN') . ': ' . $e->getMessage());
                $this->logger->debug('Payload causing failure: ' . json_encode($raw));
            }
        }

        $this->entityManager->flush();
        return $imported;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function isSupported(array $data): bool
    {
        return isset(
            $data['id'],
            $data['type'],
            $data['actor'],
            $data['repo'],
            $data['created_at']
        );
    }

    private function mapEventType(string $type): string
    {
        return match ($type) {
            'PushEvent' => EventType::COMMIT,
            'PullRequestEvent' => EventType::PULL_REQUEST,
            default => EventType::COMMENT,
        };
    }
}
