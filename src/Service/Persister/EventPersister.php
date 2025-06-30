<?php

namespace App\Service\Persister;

use App\Service\PayloadSanitizer;
use Doctrine\DBAL\Exception;

use function json_encode;

class EventPersister extends AbstractPersister
{
    public function __construct(
        private readonly PayloadSanitizer $payloadSanitizer,
    ) {
    }

    /**
     * @throws Exception
     */
    public function persistFromEvents(array $events): int
    {
        return $this->persistBy(
            events: $events,
            tableName: 'event',
            idPath: 'id',
            extractFn: fn (array $event) => [
                $event['id'],
                $event['actor']['id'],
                $event['repo']['id'],
                $event['type'],
                $event['payload']['size'] ?? 1,
                json_encode($this->payloadSanitizer->sanitize($event['payload'])),
                $event['created_at'],
                $event['comment'] ?? null,
            ],
        );
    }
}
