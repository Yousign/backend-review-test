<?php

namespace App\Repository\Repo;

use App\Dto\RepoInput;
use Doctrine\DBAL\Connection;

class DbalWriteRepoRepository implements WriteRepoRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function insert(RepoInput $repoInput): void
    {
        $sql = <<<SQL
        INSERT INTO repo (id, name, url)
        VALUES (:id, :name, :url)
        ON CONFLICT (id) DO NOTHING
SQL;

        $this->connection->executeStatement($sql, [
            'id' => $repoInput->id,
            'name' => $repoInput->name,
            'url' => $repoInput->url,
        ]);
    }

}
