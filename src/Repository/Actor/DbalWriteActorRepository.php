<?php

namespace App\Repository\Actor;

use App\Dto\ActorInput;
use Doctrine\DBAL\Connection;

class DbalWriteActorRepository implements WriteActorRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function insert(ActorInput $actorInput): void
    {
        $sql = <<<SQL
        INSERT INTO actor (id, login, url, avatar_url)
        VALUES (:id, :login, :url, :avatar_url)
        ON CONFLICT (id) DO NOTHING
SQL;

        $this->connection->executeStatement($sql, [
            'id' => $actorInput->id,
            'login' => $actorInput->login,
            'url' => $actorInput->url,
            'avatar_url' => $actorInput->avatarUrl,
        ]);
    }
}
