<?php

namespace App\Enums;

enum EventType: string
{
    case COMMIT = 'COM';
    case COMMENT = 'MSG';
    case PULL_REQUEST = 'PullRequestEvent';
    case ISSUE_COMMENT = 'IssueCommentEvent';
    case PUSH = 'PushEvent';
    case WATCH = 'WatchEvent';
    case COMMIT_COMMENT = 'CommitCommentEvent';
    case CREATE = 'CreateEvent';
    case DELETE = 'DeleteEvent';
    case FORK = 'ForkEvent';
    case GOLLUM = 'GollumEvent';
    case ISSUES = 'IssuesEvent';
    case MEMBER = 'MemberEvent';
    case PUBLIC = 'PublicEvent';
    case PULL_REQUEST_REVIEW = 'PullRequestReviewEvent';
    case PULL_REQUEST_REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
    case PULL_REQUEST_REVIEW_THREAD = 'PullRequestReviewThreadEvent';
    case RELEASE = 'ReleaseEvent';
    case SPONSORSHIP = 'SponsorshipEvent';

    public function getLabels()
    {
        return match ($this) {
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
        };

    }
}
