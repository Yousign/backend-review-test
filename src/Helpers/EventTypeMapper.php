<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enum\GithubEventType;

class EventTypeMapper
{
    /**
     * Map GH Archive event type to our internal EventType string constant
     * @param string $ghArchiveType
     * @return string|null
     */
    public function mapEventType(string $ghArchiveType): ?string
    {
        $githubEventType = GithubEventType::tryFrom($ghArchiveType);
        return $githubEventType?->toEventType();
    }

    /**
     * Check if we support this GH Archive event type
     * @param string $ghArchiveType
     * @return bool
     */
    public function isSupportedEventType(string $ghArchiveType): bool
    {
        return GithubEventType::isSupported($ghArchiveType);
    }

    /**
     * Get all supported GH Archive event types
     * @return array
     */
    public function getSupportedEventTypes(): array
    {
        return GithubEventType::getSupportedTypes();
    }

    /**
     * Get the mapping from GitHub event types to internal EventType constants
     * @return array
     */
    public function getMapping(): array
    {
        return GithubEventType::getMapping();
    }

    /**
     * Get GitHub event types that map to a specific internal EventType
     * @param string $internalType
     * @return array
     */
    public function getGitHubTypesByInternalType(string $internalType): array
    {
        return GithubEventType::getByInternalType($internalType);
    }

    /**
     * Get a human-readable description for a GitHub event type
     * @param string $ghArchiveType
     * @return string|null
     */
    public function getGitHubEventDescription(string $ghArchiveType): ?string
    {
        $githubEventType = GithubEventType::tryFrom($ghArchiveType);
        return $githubEventType?->getDescription();
    }

    /**
     * Check if a GitHub event type is comment-related
     * @param string $ghArchiveType
     * @return bool
     */
    public function isCommentEvent(string $ghArchiveType): bool
    {
        $githubEventType = GithubEventType::tryFrom($ghArchiveType);
        return $githubEventType?->isCommentEvent() ?? false;
    }

    /**
     * Check if a GitHub event type is commit-related
     * @param string $ghArchiveType
     * @return bool
     */
    public function isCommitEvent(string $ghArchiveType): bool
    {
        $githubEventType = GithubEventType::tryFrom($ghArchiveType);
        return $githubEventType?->isCommitEvent() ?? false;
    }

    /**
     * Check if a GitHub event type is pull request-related
     * @param string $ghArchiveType
     * @return bool
     */
    public function isPullRequestEvent(string $ghArchiveType): bool
    {
        $githubEventType = GithubEventType::tryFrom($ghArchiveType);
        return $githubEventType?->isPullRequestEvent() ?? false;
    }
} 