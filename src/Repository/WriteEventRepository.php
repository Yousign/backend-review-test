<?php

namespace App\Repository;

use App\Dto\EventInput;
use App\Dto\GHArchiveEvent;

interface WriteEventRepository
{
    /**
     * Batch insert a list of GHArchiveEvent
     *
     * @param iterable<GHArchiveEvent> $events
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Throwable when the transaction is rollback
     */
    public function insertGHArchiveEvents(iterable $events): void;

    public function update(EventInput $authorInput, int $id): void;
}
