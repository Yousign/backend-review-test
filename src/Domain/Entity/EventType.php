<?php

namespace App\Domain\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    /**
     * @see https://docs.github.com/en/rest/overview/github-event-types?apiVersion=2022-11-28
     */
    public const ALLOWED_EVENT_TYPES = [
        'CommitCommentEvent' => self::COMMENT,
        'IssueCommentEvent' => self::COMMENT,
        'PullRequestEvent' => self::PULL_REQUEST,
        'PullRequestReviewCommentEvent' => self::PULL_REQUEST,
        'PushEvent' => self::COMMIT,
    ];

    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];
}
