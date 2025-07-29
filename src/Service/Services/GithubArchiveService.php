<?php

declare(strict_types=1);

namespace App\Service\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\Interfaces\GithubArchiveInterface;
use App\Utils\UrlUtils;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Helpers\EventTypeMapper;
use App\Helpers\EventKeywordFilter;
use App\Entity\Event;
use App\Repository\Interfaces\ActorRepositoryInterface;
use App\Repository\Interfaces\RepoRepositoryInterface;
use App\Repository\Interfaces\EventRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class GithubArchiveService implements GithubArchiveInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EventTypeMapper $eventTypeMapper,
        private readonly EventKeywordFilter $keywordFilter,
        private readonly ActorRepositoryInterface $actorRepository,
        private readonly RepoRepositoryInterface $repoRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function dryRunImportFromGHArchive(SymfonyStyle $io, int $year, ?int $month, ?int $day, ?int $hour, string $keyword = ''): int
    {
        $processedCount = 0;
        $importedCount = 0;

        foreach ($this->fetchGithubEvents(UrlUtils::buildGithubArchiveUrl($year, $month, $day, $hour)) as $eventData) {
            $processedCount++;

            // Skip events we don't support
            if (!$this->eventTypeMapper->isSupportedEventType($eventData['type'])) {
                continue;
            }

            // Filter by keyword if provided
            if ($keyword !== '' && !$this->keywordFilter->eventMatchesKeyword($eventData, $keyword)) {
                continue;
            }

            $importedCount++;

            // Show progress every 100 events
            if ($importedCount % 100 === 0) {
                $io->info(sprintf('Processed %d events, would import %d so far...', $processedCount, $importedCount));
            }

            // Limit output for demo purposes
            if ($importedCount >= 1000) {
                $io->info('Reached 1000 events limit for demo. Use without --dry-run for full import.');
                break;
            }
        }

        return $importedCount;
    }

    public function importEventsFromGHArchive(int $year, ?int $month, ?int $day, ?int $hour, string $keyword): int
    {
        $processedCount = 0;
        $importedCount = 0;

        foreach ($this->fetchGithubEvents(UrlUtils::buildGithubArchiveUrl($year, $month, $day, $hour)) as $eventData) {
            $processedCount++;

            // Skip events we don't support
            if (!$this->eventTypeMapper->isSupportedEventType($eventData['type'])) {
                continue;
            }

            // Filter by keyword if provided
            if ($keyword !== '' && !$this->keywordFilter->eventMatchesKeyword($eventData, $keyword)) {
                continue;
            }

            try {
                // Check if event already exists
                $eventId = (int) $eventData['id'];
                if ($this->eventRepository->exist($eventId)) {
                    continue; // Skip if already imported
                }

                // Create event using repositories
                $event = $this->createEventFromGHArchiveData($eventData);
                
                $this->entityManager->persist($event);
                $importedCount++;
                
                // Flush every 100 events to avoid memory issues
                if ($importedCount % 100 === 0) {
                    $this->entityManager->flush();
                }
                
            } catch (\Exception $e) {
                // Log error but continue with other events
                error_log(sprintf('Error processing event %s: %s', $eventData['id'] ?? 'unknown', $e->getMessage()));
                continue;
            }
        }

        // Final flush
        $this->entityManager->flush();

        return $importedCount;
    }

    /**
     * Fetches GitHub events from the archive using the provided URL pattern.
     * 
     * @param string $urlPattern The URL pattern to fetch events from (may contain range expansions)
     * @return \Generator Yields event data arrays
     */
    private function fetchGithubEvents(string $urlPattern): \Generator
    {
        $urls = $this->expandUrlPattern($urlPattern);

        foreach ($urls as $url) {
            try {
                yield from $this->fetchSingleUrl($url);
            } catch (\Exception $e) {
                // Log the error but continue with next URL
                error_log("Failed to process URL {$url}: " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Fetches events from a single URL and yields them as a generator.
     * 
     * @param string $url The URL to fetch events from
     * @return \Generator Yields event data arrays
     * @throws \RuntimeException When download fails or file is empty
     */
    private function fetchSingleUrl(string $url): \Generator
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'github_archive_') . '.json.gz';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_FILE => fopen($tempFile, 'w'),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'GitHub Archive Importer/1.0',
            CURLOPT_FAILONERROR => true,
            CURLOPT_SSL_VERIFYPEER => false, // In case of SSL issues
        ]);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!$success || $httpCode !== 200) {
            if (file_exists($tempFile))
                unlink($tempFile);
            throw new \RuntimeException("Failed to download $url: HTTP $httpCode - $error");
        }

        // Check if file has content
        if (!file_exists($tempFile) || filesize($tempFile) === 0) {
            if (file_exists($tempFile))
                unlink($tempFile);
            throw new \RuntimeException("Downloaded file is empty: $url");
        }

        try {
            yield from $this->processLocalGzipFile($tempFile);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Processes a local gzip file and yields JSON event data line by line.
     * 
     * @param string $filePath Path to the gzip file to process
     * @return \Generator Yields decoded event data arrays
     * @throws \RuntimeException When the gzip file cannot be opened
     */
    private function processLocalGzipFile(string $filePath): \Generator
    {
        $lineNumber = 0;
        $gzipStream = gzopen($filePath, 'rb');

        if (!$gzipStream) {
            throw new \RuntimeException("Failed to open gzip file: $filePath");
        }

        try {
            while (($line = fgets($gzipStream)) !== false) {
                $lineNumber++;

                if (empty(trim($line))) {
                    continue;
                }

                $event = json_decode($line, true);
                if ($event === null) {
                    continue; // Skip invalid JSON
                }

                yield $event;

                // Free memory every 1000 lines to avoid memory issues
                if ($lineNumber % 1000 === 0) {
                    gc_collect_cycles();
                }
            }
        } finally {
            gzclose($gzipStream);
        }
    }

    /**
     * Expands URL patterns containing range expressions like {0..23} into individual URLs.
     * 
     * @param string $urlPattern The URL pattern that may contain range expansions
     * @return array Array of expanded URLs
     */
    private function expandUrlPattern(string $urlPattern): array
    {
        $urls = [];

        // Handle patterns like {0..23} or {01..31}
        if (preg_match_all('/\{(\d+)\.\.(\d+)\}/', $urlPattern, $matches, PREG_SET_ORDER)) {
            $currentPattern = $urlPattern;

            foreach ($matches as $match) {
                $start = (int) $match[1];
                $end = (int) $match[2];
                $newUrls = [];

                if (empty($urls)) {
                    // First expansion
                    for ($i = $start; $i <= $end; $i++) {
                        $padding = strlen($match[1]); // Preserve zero-padding
                        $replacement = str_pad((string) $i, $padding, '0', STR_PAD_LEFT);
                        $newUrls[] = str_replace($match[0], $replacement, $currentPattern);
                    }
                    $urls = $newUrls;
                } else {
                    // Subsequent expansions (for patterns with multiple ranges)
                    foreach ($urls as $existingUrl) {
                        for ($i = $start; $i <= $end; $i++) {
                            $padding = strlen($match[1]);
                            $replacement = str_pad((string) $i, $padding, '0', STR_PAD_LEFT);
                            $newUrls[] = str_replace($match[0], $replacement, $existingUrl);
                        }
                    }
                    $urls = $newUrls;
                }
            }
        } else {
            // No pattern to expand, return single URL
            $urls = [$urlPattern];
        }

        return $urls;
    }

    /**
     * @param array<string, mixed> $eventData
     */
    private function createEventFromGHArchiveData(array $eventData): Event
    {
        $eventType = $this->eventTypeMapper->mapEventType($eventData['type']);
        if ($eventType === null) {
            throw new \InvalidArgumentException(sprintf('Unsupported event type: %s', $eventData['type']));
        }
        
        // Create or get Actor using repository
        $actor = $this->actorRepository->findOrCreate($eventData['actor']);
        
        // Create or get Repo using repository
        $repo = $this->repoRepository->findOrCreate($eventData['repo']);
        
        // Extract comment based on event type
        $comment = $this->extractCommentFromEvent($eventData);
        
        // Create Event
        return new Event(
            (int) $eventData['id'],
            $eventType,
            $actor,
            $repo,
            $eventData['payload'] ?? [],
            new \DateTimeImmutable($eventData['created_at']),
            $comment
        );
    }

    /**
     * @param array<string, mixed> $eventData
     */
    private function extractCommentFromEvent(array $eventData): ?string
    {
        $githubEventType = \App\Enum\GitHubEventType::tryFrom($eventData['type']);
        if ($githubEventType === null) {
            return null;
        }

        return match ($githubEventType) {
            \App\Enum\GitHubEventType::ISSUE_COMMENT_EVENT,
            \App\Enum\GitHubEventType::COMMIT_COMMENT_EVENT,
            \App\Enum\GitHubEventType::PULL_REQUEST_REVIEW_COMMENT_EVENT => 
                $eventData['payload']['comment']['body'] ?? null,
                
            \App\Enum\GitHubEventType::PULL_REQUEST_EVENT => 
                $this->extractPullRequestComment($eventData),
                
            \App\Enum\GitHubEventType::PUSH_EVENT => 
                $this->extractCommitMessages($eventData),
        };
    }

    /**
     * @param array<string, mixed> $eventData
     */
    private function extractPullRequestComment(array $eventData): ?string
    {
        $pr = $eventData['payload']['pull_request'] ?? [];
        return $pr['body'] ?? $pr['title'] ?? null;
    }

    /**
     * @param array<string, mixed> $eventData
     */
    private function extractCommitMessages(array $eventData): ?string
    {
        if (isset($eventData['payload']['commits']) && !empty($eventData['payload']['commits'])) {
            $messages = array_map(fn($commit) => $commit['message'] ?? '', $eventData['payload']['commits']);
            return implode("\n", array_filter($messages));
        }
        return null;
    }
}