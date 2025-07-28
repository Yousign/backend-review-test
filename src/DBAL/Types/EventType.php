<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * @extends AbstractEnumType<string, string>
 */
class EventType extends AbstractEnumType
{
    public const string COMMIT = 'COM';
    public const string COMMENT = 'MSG';
    public const string PULL_REQUEST = 'PullRequestEvent';
    public const string ISSUE_COMMENT = 'IssueCommentEvent';
    public const string PUSH = 'PushEvent';
    public const string WATCH = 'WatchEvent';
    public const string COMMIT_COMMENT = 'CommitCommentEvent';
    public const string CREATE = 'CreateEvent';
    public const string DELETE = 'DeleteEvent';
    public const string FORK = 'ForkEvent';
    public const string GOLLUM = 'GollumEvent';
    public const string ISSUES = 'IssuesEvent';
    public const string MEMBER = 'MemberEvent';
    public const string PUBLIC = 'PublicEvent';
    public const string PULL_REQUEST_REVIEW = 'PullRequestReviewEvent';
    public const string PULL_REQUEST_REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
    public const string PULL_REQUEST_REVIEW_THREAD = 'PullRequestReviewThreadEvent';
    public const string RELEASE = 'ReleaseEvent';
    public const string SPONSORSHIP = 'SponsorshipEvent';

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
        self::PULL_REQUEST_REVIEW_COMMENT => 'Pull Request Review Comment',
        self::PULL_REQUEST_REVIEW_THREAD => 'Pull Request Review Thread',
        self::RELEASE => 'Release',
        self::SPONSORSHIP => 'Sponsorship',
    ];
}
