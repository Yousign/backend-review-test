<?php

namespace App\Service\Services;

use App\Dto\EventInput;
use App\Dto\SearchInput;
use App\Repository\Interfaces\EventRepositoryInterface;
use App\Service\Interfaces\EventServiceInterface;

/**
 * Service for Event business operations.
 * 
 * This service provides a business logic layer on top of the EventRepository.
 * It handles validation, data transformation, and orchestrates repository operations
 * to provide a clean API for controllers.
 * 
 * @package App\Service
 */
class EventService implements EventServiceInterface
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository
    ) {
    }

    /**
     * Get comprehensive search results for events.
     * 
     * Returns a structured array containing metadata and data for events
     * matching the search criteria.
     * 
     * @param SearchInput $searchInput The search criteria
     * @return array<string, mixed> Structured search results with meta and data
     */
    public function searchEvents(SearchInput $searchInput): array
    {
        $countByType = $this->eventRepository->countByType($searchInput);

        return [
            'meta' => [
                'totalEvents' => $this->eventRepository->countAll($searchInput),
                'totalPullRequests' => $countByType['pullRequest'] ?? 0,
                'totalCommits' => $countByType['commit'] ?? 0,
                'totalComments' => $countByType['comment'] ?? 0,
            ],
            'data' => [
                'events' => $this->eventRepository->getLatest($searchInput),
                'stats' => $this->eventRepository->statsByTypePerHour($searchInput)
            ]
        ];
    }

    /**
     * Update an event's comment.
     * 
     * Validates that the event exists before attempting to update it.
     * 
     * @param EventInput $eventInput The input data containing the new comment
     * @param int $id The ID of the event to update
     * @throws \InvalidArgumentException When the event with the given ID is not found
     */
    public function updateEvent(EventInput $eventInput, int $id): void
    {
        if (!$this->eventRepository->exist($id)) {
            throw new \InvalidArgumentException(sprintf('Event identified by %d not found!', $id));
        }

        $this->eventRepository->update($eventInput, $id);
    }

    /**
     * Check if an event exists.
     * 
     * @param int $id The event ID to check
     * @return bool True if the event exists, false otherwise
     */
    public function eventExists(int $id): bool
    {
        return $this->eventRepository->exist($id);
    }
} 