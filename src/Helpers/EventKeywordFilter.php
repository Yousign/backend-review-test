<?php

declare(strict_types=1);

namespace App\Helpers;

class EventKeywordFilter
{
    public function __construct(
        private readonly EventTypeMapper $eventTypeMapper
    ) {
    }

    /**
     * Check if an event matches the given keyword
     */
    public function eventMatchesKeyword(array $event, string $keyword): bool
    {
        $keyword = strtolower($keyword);
        
        // Check in actor login
        if (isset($event['actor']['login']) && 
            stripos($event['actor']['login'], $keyword) !== false) {
            return true;
        }
        
        // Check in repo name
        if (isset($event['repo']['name']) && 
            stripos($event['repo']['name'], $keyword) !== false) {
            return true;
        }
        
        // Check in commit messages for PushEvent
        if ($this->eventTypeMapper->isCommitEvent($event['type']) && isset($event['payload']['commits'])) {
            foreach ($event['payload']['commits'] as $commit) {
                if (isset($commit['message']) && 
                    stripos($commit['message'], $keyword) !== false) {
                    return true;
                }
            }
        }
        
        // Check in pull request title/body
        if ($this->eventTypeMapper->isPullRequestEvent($event['type']) && isset($event['payload']['pull_request'])) {
            $pr = $event['payload']['pull_request'];
            if ((isset($pr['title']) && stripos($pr['title'], $keyword) !== false) ||
                (isset($pr['body']) && stripos($pr['body'], $keyword) !== false)) {
                return true;
            }
        }
        
        // Check in comment body
        if ($this->eventTypeMapper->isCommentEvent($event['type']) && 
            isset($event['payload']['comment']['body'])) {
            if (stripos($event['payload']['comment']['body'], $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
} 