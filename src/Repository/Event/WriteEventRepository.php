<?php

namespace App\Repository\Event;

use App\Dto\EventInput;

interface WriteEventRepository
{
    public function insert(EventInput $eventInput): void;

    public function update(EventInput $eventInput, int $id): void;
}
