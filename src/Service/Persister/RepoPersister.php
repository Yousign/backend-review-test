<?php

namespace App\Service\Persister;

use Doctrine\DBAL\Exception;

class RepoPersister extends AbstractPersister
{
    /**
     * @throws Exception
     */
    public function persistFromEvents(array $events): int
    {
        return $this->persistBy(
            events: $events,
            tableName: 'repo',
            idPath: 'repo.id',
            extractFn: fn (array $event) => [
                $event['repo']['id'],
                $event['repo']['name'],
                $event['repo']['url'],
            ],
        );
    }
}
