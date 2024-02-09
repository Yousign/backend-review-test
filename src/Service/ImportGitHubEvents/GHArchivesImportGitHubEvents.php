<?php

declare(strict_types=1);

namespace App\Service\ImportGitHubEvents;

use LogicException;
use App\Service\ImportGitHubEvents\Storage\EventStore;
use App\Service\ImportGitHubEvents\Provider\GHArchivesEventsProvider;
use App\Service\ImportGitHubEvents\Transformer\GHArchivesEventsTransformer;

final class GHArchivesImportGitHubEvents implements ImportGitHubEvents
{
    private string $query;

    public function __construct(
        private GHArchivesEventsProvider $provider,
        private GHArchivesEventsTransformer $transformer,
        private EventStore $eventStore,
    ) {
    }

    public function setQuery(string $query): void
    {
        if (!\preg_match(pattern: '#\d{4}-\d{2}-\d{2}-(\d{1,2}|\{\d{1,2}\.\.\d{1,2}\})#', subject: $query)) {
            throw new LogicException('Query must follow format YYYY-MM-DD-h.');
        }
        $this->query = $query;
    }

    public function execute(): void
    {
        $archivesEvents = $this->provider->fetch($this->query);
        $events = $this->transformer->transformToEvents($archivesEvents);
        $this->eventStore->storeEvents($events);
    }
}
