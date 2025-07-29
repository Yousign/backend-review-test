<?php

declare(strict_types=1);

namespace App\Enum;

use App\Entity\EventType;

/**
 * Enum for mapping GitHub Archive event types to our internal EventType constants
 */
enum GitHubEventType: string
{
    case PUSH_EVENT = 'PushEvent';
    case PULL_REQUEST_EVENT = 'PullRequestEvent';
    case ISSUE_COMMENT_EVENT = 'IssueCommentEvent';
    case COMMIT_COMMENT_EVENT = 'CommitCommentEvent';
    case PULL_REQUEST_REVIEW_COMMENT_EVENT = 'PullRequestReviewCommentEvent';

    /**
     * Get the corresponding internal EventType for this GitHub event type
     */
    public function toEventType(): string
    {
        return match ($this) {
            self::PUSH_EVENT => EventType::COMMIT,
            self::PULL_REQUEST_EVENT => EventType::PULL_REQUEST,
            self::ISSUE_COMMENT_EVENT,
            self::COMMIT_COMMENT_EVENT,
            self::PULL_REQUEST_REVIEW_COMMENT_EVENT => EventType::COMMENT,
        };
    }

    /**
     * Check if this event type is supported
     */
    public static function isSupported(string $eventType): bool
    {
        return self::tryFrom($eventType) !== null;
    }

    /**
     * Get all supported GitHub event types
     */
    public static function getSupportedTypes(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the mapping from GitHub event types to internal EventType constants
     */
    public static function getMapping(): array
    {
        $mapping = [];
        foreach (self::cases() as $case) {
            $mapping[$case->value] = $case->toEventType();
        }
        return $mapping;
    }

    /**
     * Get event types that map to a specific internal EventType
     */
    public static function getByInternalType(string $internalType): array
    {
        $types = [];
        foreach (self::cases() as $case) {
            if ($case->toEventType() === $internalType) {
                $types[] = $case->value;
            }
        }
        return $types;
    }

    /**
     * Get a human-readable description for this event type
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::PUSH_EVENT => 'Code push to repository',
            self::PULL_REQUEST_EVENT => 'Pull request created, updated, or closed',
            self::ISSUE_COMMENT_EVENT => 'Comment on an issue',
            self::COMMIT_COMMENT_EVENT => 'Comment on a commit',
            self::PULL_REQUEST_REVIEW_COMMENT_EVENT => 'Comment on a pull request review',
        };
    }

    /**
     * Check if this event type is comment-related
     */
    public function isCommentEvent(): bool
    {
        return in_array($this, [
            self::ISSUE_COMMENT_EVENT,
            self::COMMIT_COMMENT_EVENT,
            self::PULL_REQUEST_REVIEW_COMMENT_EVENT,
        ]);
    }

    /**
     * Check if this event type is commit-related
     */
    public function isCommitEvent(): bool
    {
        return $this === self::PUSH_EVENT;
    }

    /**
     * Check if this event type is pull request-related
     */
    public function isPullRequestEvent(): bool
    {
        return $this === self::PULL_REQUEST_EVENT;
    }
} 