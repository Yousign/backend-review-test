<?php

namespace App\Service\Interfaces;

use App\Dto\EventInput;
use App\Dto\SearchInput;

/**
 * Interface for Event service operations.
 * 
 * This interface defines the business logic operations for Event entities.
 * It provides a clean API for controllers to interact with event data
 * without directly accessing the repository layer.
 * 
 * @package App\Service\Interfaces
 */
interface EventServiceInterface
{
    /**
     * Get comprehensive search results for events.
     * 
     * Returns a structured array containing metadata and data for events
     * matching the search criteria. This method orchestrates multiple
     * repository calls to provide a complete search result.
     * 
     * @param SearchInput $searchInput The search criteria containing date and keyword
     * @return array<string, mixed> Structured search results with meta and data sections
     */
    public function searchEvents(SearchInput $searchInput): array;

    /**
     * Update an event's comment.
     * 
     * Validates that the event exists before attempting to update it.
     * This method provides business logic validation and error handling
     * for event updates.
     * 
     * @param EventInput $eventInput The input data containing the new comment
     * @param int $id The ID of the event to update
     * @throws \InvalidArgumentException When the event with the given ID is not found
     */
    public function updateEvent(EventInput $eventInput, int $id): void;

    /**
     * Check if an event exists.
     * 
     * Provides a simple way to verify event existence without exposing
     * repository details to the controller layer.
     * 
     * @param int $id The event ID to check
     * @return bool True if the event exists, false otherwise
     */
    public function eventExists(int $id): bool;
} 