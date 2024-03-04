<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * @see https://docs.github.com/en/rest/using-the-rest-api/github-event-types?apiVersion=2022-11-28
 */
class EventType extends AbstractEnumType
{
    public const COMMIT_COMMENT = 'CommitCommentEvent';
    public const CREATE = 'CreateEvent';
    public const DELETE = 'DeleteEvent';
    public const FORK = 'ForkEvent';
    public const GOLLUM = 'GollumEvent';
    public const ISSUE_COMMENT = 'IssueCommentEvent';
    public const ISSUES = 'IssuesEvent';
    public const MEMBER = 'MemberEvent';
    public const PUBLIC = 'PublicEvent';
    public const PULL_REQUEST = 'PullRequestEvent';
    public const PULL_REQUEST_REVIEW = 'PullRequestReviewEvent';
    public const PULL_REQUEST_REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
    public const PULL_REQUEST_REVIEW_THREAD = 'PullRequestReviewThreadEvent';
    public const PUSH = 'PushEvent';
    public const RELEASE = 'ReleaseEvent';
    public const SPONSORSHIP = 'SponsorshipEvent';
    public const WATCH = 'WatchEvent';

    protected static array $choices = [
        self::COMMIT_COMMENT => 'Commit Comment',
        self::CREATE => 'Create',
        self::DELETE => 'Delete',
        self::FORK => 'Fork',
        self::GOLLUM => 'Gollum',
        self::ISSUE_COMMENT => 'Issue Comment',
        self::ISSUES => 'Issues',
        self::MEMBER => 'Member',
        self::PUBLIC => 'Public',
        self::PULL_REQUEST => 'Pull Request',
        self::PULL_REQUEST_REVIEW => 'Pull Request Review',
        self::PULL_REQUEST_REVIEW_COMMENT => 'Pull Request Review Comment',
        self::PULL_REQUEST_REVIEW_THREAD => 'Pull Request Review Thread',
        self::PUSH => 'Push',
        self::RELEASE => 'Release',
        self::SPONSORSHIP => 'Sponsorship',
        self::WATCH => 'Watch',
    ];
}
