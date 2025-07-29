<?php

namespace App\Repository\Interfaces;

use App\Dto\EventInput;
use App\Dto\SearchInput;

/**
 * Interface for Event repository operations.
 * 
 * This interface defines all read and write operations for Event entities.
 * It provides a unified API for managing GitHub events including commits,
 * pull requests, and comments with search capabilities.
 * 
 * @package App\Repository
 */
interface EventRepositoryInterface
{
    /**
     * Count all events matching the search criteria.
     * 
     * Returns the total count of events for a specific date that contain
     * the specified keyword in their payload.
     * 
     * @param SearchInput $searchInput The search criteria containing date and keyword
     * @return int The total count of matching events
     */
    public function countAll(SearchInput $searchInput): int;
    
    /**
     * Count events grouped by type.
     * 
     * Returns an associative array where keys are event types (commit, pullRequest, comment)
     * and values are the count of events for each type matching the search criteria.
     * 
     * @param SearchInput $searchInput The search criteria containing date and keyword
     * @return array<string, int> Associative array with event types as keys and counts as values
     */
    public function countByType(SearchInput $searchInput): array;
    
    /**
     * Get hourly statistics by event type.
     * 
     * Returns a 24-hour array where each hour contains counts for different event types.
     * This is useful for generating time-based analytics and charts.
     * 
     * @param SearchInput $searchInput The search criteria containing date and keyword
     * @return array<int, array<string, int>> 24-hour array with hourly event type counts
     */
    public function statsByTypePerHour(SearchInput $searchInput): array;
    
    /**
     * Get latest events matching search criteria.
     * 
     * Returns an array of the most recent events with their type and repository information.
     * 
     * @param SearchInput $searchInput The search criteria containing date and keyword
     * @return array<int, array<string, mixed>> Array of events with type and repo data
     */
    public function getLatest(SearchInput $searchInput): array;
    
    /**
     * Check if an event with the given ID exists.
     * 
     * @param int $id The event ID to check
     * @return bool True if the event exists, false otherwise
     */
    public function exist(int $id): bool;
    
    /**
     * Update an existing event's comment.
     * 
     * Updates the comment field of an event identified by its ID.
     * Throws an exception if the event is not found.
     * 
     * @param EventInput $authorInput The input data containing the new comment
     * @param int $id The ID of the event to update
     * @throws \InvalidArgumentException When the event with the given ID is not found
     */
    public function update(EventInput $authorInput, int $id): void;
} 