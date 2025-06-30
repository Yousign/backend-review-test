<?php

namespace App\Repository\Event;

use App\Dto\EventInput;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function insert(EventInput $eventInput): void
    {
        $sql = <<<SQL
        INSERT INTO event (id, actor_id, repo_id, type, count, payload, created_at, comment)
        VALUES (:id, :actor_id, :repo_id, :type, :count, :payload, :created_at, :comment)
        ON CONFLICT (id) DO NOTHING
SQL;

        $this->connection->executeStatement($sql, [
            'id' => $eventInput->id,
            'actor_id' => $eventInput->actorId,
            'repo_id' => $eventInput->repoId,
            'type' => $eventInput->type->value,
            'count' => $eventInput->count,
            'payload' => json_encode($eventInput->payload),
            'created_at' => $eventInput->createdAt->format('Y-m-d H:i:s'),
            'comment' => $eventInput->comment,
        ]);
    }

    public function update(EventInput $eventInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $eventInput->comment]);
    }
}
