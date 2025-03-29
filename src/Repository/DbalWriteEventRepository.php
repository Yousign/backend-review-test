<?php

namespace App\Repository;

use App\Dto\EventInput;
use Doctrine\DBAL\Connection;

readonly class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }
}
