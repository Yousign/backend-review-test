<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PullRequestEvent';
    public const ISSUE_COMMENT = 'IssueCommentEvent';
    public const PUSH = 'PushEvent';
    public const WATCH = 'WatchEvent';
    public const COMMIT_COMMENT = 'CommitCommentEvent';
    public const CREATE = 'CreateEvent';
    public const DELETE = 'DeleteEvent';
    public const FORK = 'ForkEvent';
    public const GOLLUM = 'GollumEvent';
    public const ISSUES = 'IssuesEvent';
    public const MEMBER = 'MemberEvent';
    public const PUBLIC = 'PublicEvent';
    public const PULL_REQUEST_REVIEW = 'PullRequestReviewEvent';
    public const PULL_REQUESTRE_VIEW_COMMENT = 'PullRequestReviewCommentEvent';
    public const PULL_REQUEST_REVIEW_THREAD = 'PullRequestReviewThreadEvent';
    public const RELEASE = 'ReleaseEvent';
    public const SPONSORSHIP = 'SponsorshipEvent';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
        self::ISSUE_COMMENT => 'Issue Comment',
        self::PUSH => 'Push',
        self::WATCH => 'Watch',
        self::COMMIT_COMMENT => 'Commit Comment',
        self::CREATE => 'Create',
        self::DELETE => 'Delete',
        self::FORK => 'Fork',
        self::GOLLUM => 'Gollum',
        self::ISSUES => 'Issues',
        self::MEMBER => 'Member',
        self::PUBLIC => 'Public',
        self::PULL_REQUEST_REVIEW => 'Pull Request Review',
        self::PULL_REQUESTRE_VIEW_COMMENT => 'Pull Request Review Comment',
        self::PULL_REQUEST_REVIEW_THREAD => 'Pull Request Review Thread',
        self::RELEASE => 'Release',
        self::SPONSORSHIP => 'Sponsorship',
    ];
}
