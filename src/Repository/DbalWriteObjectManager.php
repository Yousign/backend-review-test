<?php

namespace App\Repository;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use Doctrine\DBAL\Connection;

class DbalWriteObjectManager
{
    private const SQL_INSERT = [
        Repo::class => 'INSERT INTO repo (id, name, url) VALUES ',
        Actor::class => 'INSERT INTO actor (id, login, url, avatar_url) VALUES ',
        Event::class => 'INSERT INTO event (id, type, count, repo_id, actor_id, payload, create_at, comment) VALUES ',
    ];

    private const SQL_ON_CONFLICT = [
        Repo::class => ' ON CONFLICT (id) DO UPDATE SET name = excluded.name, url = excluded.url',
        Actor::class => ' ON CONFLICT (id) DO UPDATE SET login = excluded.login, url = excluded.url, avatar_url = excluded.avatar_url',
        Event::class => ' ON CONFLICT (id) DO NOTHING',
    ];

    private const SQL_PLACEHOLDERS = [
        Repo::class => '(?, ?, ?)',
        Actor::class => '(?, ?, ?, ?)',
        Event::class => '(?, ?, ?, ?, ?, ?, ?, ?)',
    ];

    public function __construct(
        private Connection $connection
    ) {}

    public function upsert(string $class, array $objects): void
    {
        $sql = self::SQL_INSERT[$class];
        $placeholders = array_fill(0, count($objects), self::SQL_PLACEHOLDERS[$class]);
        $sql .= implode(', ', $placeholders);
        $sql .= self::SQL_ON_CONFLICT[$class];

        $values = [];
        foreach ($objects as $object) {
            $values = array_merge($values, $object);
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement($values);
    }
}
