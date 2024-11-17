<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];

    public static array $eventTypeMapping = [
        'PushEvent' => self::COMMIT,
        'PullRequestEvent' => self::PULL_REQUEST,
        'CommitCommentEvent' => self::COMMENT,
        'IssueCommentEvent' => self::COMMENT,
        'PullRequestReviewCommentEvent' => self::COMMENT
    ];
}
