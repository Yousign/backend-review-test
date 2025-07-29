<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum for internal event types used in our application
 */
enum LocalEventType: string 
{
    case COMMIT = 'COM';
    case PULL_REQUEST = 'PR';
    case COMMENT = 'MSG';

    /**
     * Get a human-readable name for this event type
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::COMMIT => 'Commit',
            self::PULL_REQUEST => 'Pull Request',
            self::COMMENT => 'Comment',
        };
    }

    /**
     * Get a short description for this event type
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::COMMIT => 'Code commits and pushes',
            self::PULL_REQUEST => 'Pull request activities',
            self::COMMENT => 'Comments on issues, commits, or pull requests',
        };
    }

    /**
     * Check if this event type is comment-related
     */
    public function isCommentType(): bool
    {
        return $this === self::COMMENT;
    }

    /**
     * Check if this event type is commit-related
     */
    public function isCommitType(): bool
    {
        return $this === self::COMMIT;
    }

    /**
     * Check if this event type is pull request-related
     */
    public function isPullRequestType(): bool
    {
        return $this === self::PULL_REQUEST;
    }

    /**
     * Get all internal event types
     */
    public static function getAll(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all internal event types with their display names
     */
    public static function getAllWithDisplayNames(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->getDisplayName();
        }
        return $result;
    }

    /**
     * Validate if a string value is a valid internal event type
     */
    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    /**
     * Create from string value with validation
     */
    public static function fromString(string $value): self
    {
        $enum = self::tryFrom($value);
        if ($enum === null) {
            throw new \InvalidArgumentException(sprintf('Invalid internal event type: %s', $value));
        }
        return $enum;
    }
} 