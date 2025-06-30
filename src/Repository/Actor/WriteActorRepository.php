<?php

namespace App\Repository\Actor;

use App\Dto\ActorInput;

interface WriteActorRepository
{
    public function insert(ActorInput $actorInput): void;
}
