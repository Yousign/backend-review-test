<?php

namespace App\Service\Persister;

class ActorPersister extends AbstractPersister
{
    public function persistFromEvents(array $events): int
    {
        return $this->persistBy(
            events: $events,
            tableName: 'actor',
            idPath: 'actor.id',
            extractFn: fn (array $event) => [
                $event['actor']['id'],
                $event['actor']['login'],
                $event['actor']['url'],
                $event['actor']['avatar_url'],
            ],
        );
    }
}
