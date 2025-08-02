<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\EventInput;
use App\Repository\Interfaces\WriteEventRepositoryInterface;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepositoryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
