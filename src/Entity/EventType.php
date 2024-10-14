<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';

    /**
     * @var array<string, self::*>
     *
     * @see https://docs.github.com/en/rest/using-the-rest-api/github-event-types
     */
    public const GH_ARCHIVE_MAPPING = [
        'CommitCommentEvent' => self::COMMIT,
        'IssueCommentEvent' => self::COMMENT,
        'PullRequestEvent' => self::PULL_REQUEST,
        'PullRequestReviewCommentEvent' => self::COMMENT,
        'PushEvent' => self::COMMENT,
    ];

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];
}
