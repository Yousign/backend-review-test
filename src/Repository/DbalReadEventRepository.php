<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\SearchInput;
use App\Repository\Interfaces\ReadEventRepositoryInterface;
use Doctrine\DBAL\Connection;

class DbalReadEventRepository implements ReadEventRepositoryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
        SELECT SUM(count) as count
        FROM event
        WHERE created_at >= :startDate
        AND created_at <= :endDate
        AND payload::text like :keyword
SQL;

        return (int) $this->connection->fetchOne($sql, [
            'startDate' => $searchInput->date->format('Y-m-d 00:00:00'),
            'endDate' => $searchInput->date->format('Y-m-d 23:59:59'),
            'keyword' => '%' . $searchInput->keyword . '%',
        ]);
    }

    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<SQL
        SELECT type, SUM(count) AS count
        FROM event
        WHERE created_at >= :startDate
        AND created_at <= :endDate
          AND payload::text LIKE :keyword
        GROUP BY type
    SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'startDate' => $searchInput->date->format('Y-m-d 00:00:00'),
            'endDate' => $searchInput->date->format('Y-m-d 23:59:59'),
            'keyword' => '%' . $searchInput->keyword . '%',
        ]);
    }

    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
        SELECT EXTRACT(hour from created_at) as hour, type, SUM(count) as count
        FROM event
        WHERE created_at >= :startDate
        AND created_at <= :endDate
        AND payload::text LIKE :keyword
        GROUP BY type, EXTRACT(hour from created_at)
SQL;

        $stats = $this->connection->fetchAllAssociative($sql, [
            'startDate' => $searchInput->date->format('Y-m-d 00:00:00'),
            'endDate' => $searchInput->date->format('Y-m-d 23:59:59'),
            'keyword' => '%' . $searchInput->keyword . '%',
        ]);

        $data = array_fill(0, 24, ['commit' => 0, 'pullRequest' => 0, 'comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = (int) $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
        SELECT e.type, r.id as repo_id, r.name as repo_name
        FROM event e
        JOIN repo r ON e.repo_id = r.id
        WHERE e.created_at >= :startDate
        AND e.created_at <= :endDate
        AND e.payload::text LIKE :keyword
        ORDER BY e.created_at DESC
        LIMIT 10
SQL;

        return $this->connection->fetchAllAssociative($sql, [
            'startDate' => $searchInput->date->format('Y-m-d 00:00:00'),
            'endDate' => $searchInput->date->format('Y-m-d 23:59:59'),
            'keyword' => '%' . $searchInput->keyword . '%',
        ]);
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM event
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id,
        ]);

        return (bool) $result;
    }
}
