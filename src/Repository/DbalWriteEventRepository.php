<?php

namespace App\Repository;

use App\Dto\EventInput;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DbalWriteEventRepository implements WriteEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
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
