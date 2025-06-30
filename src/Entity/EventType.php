<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const string COMMIT_COMMENT_EVENT = 'CommitCommentEvent';
    public const string CREATE_EVENT = 'CreateEvent';
    public const string DELETE_EVENT = 'DeleteEvent';
    public const string FORK_EVENT = 'ForkEvent';
    public const string GOLLUM_EVENT = 'GollumEvent';
    public const string ISSUE_COMMENT_EVENT = 'IssueCommentEvent';
    public const string ISSUES_EVENT = 'IssuesEvent';
    public const string MEMBER_EVENT = 'MemberEvent';
    public const string PUBLIC_EVENT = 'PublicEvent';
    public const string PULL_REQUEST_EVENT = 'PullRequestEvent';
    public const string PULL_REQUEST_REVIEW_EVENT = 'PullRequestReviewEvent';
    public const string PULL_REQUEST_REVIEW_COMMENT_EVENT = 'PullRequestReviewCommentEvent';
    public const string PULL_REQUEST_REVIEW_THREAD_EVENT = 'PullRequestReviewThreadEvent';
    public const string PUSH_EVENT = 'PushEvent';
    public const string RELEASE_EVENT = 'ReleaseEvent';
    public const string SPONSORSHIP_EVENT = 'SponsorshipEvent';
    public const string WATCH_EVENT = 'WatchEvent';

    protected static array $choices = [
        self::COMMIT_COMMENT_EVENT => 'Commit Comment',
        self::CREATE_EVENT => 'Create',
        self::DELETE_EVENT => 'Delete',
        self::FORK_EVENT => 'Fork',
        self::GOLLUM_EVENT => 'Gollum',
        self::ISSUE_COMMENT_EVENT => 'Issue Comment',
        self::ISSUES_EVENT => 'Issues',
        self::MEMBER_EVENT => 'Member',
        self::PUBLIC_EVENT => 'Public',
        self::PULL_REQUEST_EVENT => 'Pull Request',
        self::PULL_REQUEST_REVIEW_EVENT => 'Pull Request Review',
        self::PULL_REQUEST_REVIEW_COMMENT_EVENT => 'Pull Request Review Comment',
        self::PULL_REQUEST_REVIEW_THREAD_EVENT => 'Pull Request Review Thread',
        self::PUSH_EVENT => 'Push',
        self::RELEASE_EVENT => 'Release',
        self::SPONSORSHIP_EVENT => 'Sponsorship',
        self::WATCH_EVENT => 'Watch',
    ];
}
