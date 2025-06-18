<?php

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Factory\ActorFactory;
use App\Factory\RepoFactory;
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
            if (!is_array($raw) || !$this->isSupported($raw)  || !is_string($raw['type']) || !is_string($raw['created_at'])) {
                $this->logger->warning("⚠️ Unsupported event data: " . json_encode($raw));
                continue;
            }
            if(isset($raw['id']) && is_string($raw['id'])) {
                $raw['id'] = intval($raw['id']);
            }
            else{
                $this->logger->warning("⚠️ Event ID is missing or not a valid integer, skipping event.");
                continue;
            }

            try {
                $actorData = $raw['actor'];
                if (!is_array($actorData) || !isset($actorData['id'], $actorData['login'], $actorData['url'], $actorData['avatar_url'])) {
                    $this->logger->warning("⚠️ Malformed actor data, skipping.");
                    continue;
                }

                $actorId = $actorData['id'];
                if (is_int($actorId) && $actorId <= 0 ) {
                    $this->logger->warning("⚠️ Invalid actor ID: $actorId, skipping event.");
                    continue;
                }

                $actor = $this->entityManager->getRepository(Actor::class)->find($actorId)
                    ?? ActorFactory::fromArray($actorData);
                $this->entityManager->persist($actor);

                $repoData = $raw['repo'];
                if (!is_array($repoData) || !isset($repoData['id'], $repoData['name'], $repoData['url'])) {
                    $this->logger->warning("⚠️ Malformed repo data, skipping.");
                    continue;
                }

                $repoId = $repoData['id'];
                if (is_int($repoId) && $repoId <= 0) {
                    $this->logger->warning("⚠️ Invalid repo ID: $repoId, skipping event.");
                    continue;
                }

                $repo = $this->entityManager->getRepository(Repo::class)->find($repoId)
                    ?? RepoFactory::fromArray($repoData);
                $this->entityManager->persist($repo);

                $eventId = $raw['id'];
                if($eventId <= 0) {
                    $this->logger->warning("⚠️ Invalid event ID : $eventId, skipping event.");
                    continue;
                }
                if ($this->entityManager->getRepository(Event::class)->find($eventId)) {
                    $this->logger->info("ℹ️ Event ID $eventId already exists. Skipping.");
                    continue;
                }

                /** @var array<string, string|int|array<string,string|int|array<string,string|int>>> $payload */
                $payload = is_array($raw['payload'] ?? null) ? $raw['payload'] : [];
                $comment = null;
                if (
                    isset($payload['comment']) &&
                    is_array($payload['comment']) &&
                    isset($payload["comment"]['body']) &&
                    is_string($payload['comment']['body']))
                {
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
                $this->logger->error("❌ Failed to import event ID : ". $raw["id"] . " => " . $e->getMessage());
                $this->logger->debug('Payload causing failure: ' . json_encode($raw));
            }
        }

        if ($imported > 0) {
            $this->entityManager->flush();
            $this->logger->info("✅ Successfully imported $imported events from GitHub archive.");
        } else {
            $this->logger->info("No valid events found to import from GitHub archive.");
        }
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
