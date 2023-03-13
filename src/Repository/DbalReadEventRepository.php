<?php

namespace App\Repository;

use App\Dto\SearchInput;
use Doctrine\DBAL\Connection;

class DbalReadEventRepository implements ReadEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
        SELECT sum(count) as count
        FROM event
        WHERE date(create_at) = :date
        AND (payload)::text like '%{$searchInput->keyword}%'
SQL;

        return (int) $this->connection->fetchOne($sql, [
            'date' => $searchInput->date->format('Y-m-d')
        ]);
    }

    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND (payload)::text like '%{$searchInput->keyword}%'
            GROUP BY type
SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date->format('Y-m-d')
        ]);
    }

    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT extract(hour from create_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND (payload)::text like '%{$searchInput->keyword}%'
            GROUP BY TYPE, EXTRACT(hour from create_at)
SQL;

        $stats = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date->format('Y-m-d')
        ]);

        $data = array_fill(0, 24, ['COM' => 0, 'PR' => 0, 'MSG' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, ( SELECT json_build_object(
                'id', json_agg(repo.id),
                'name', json_agg(repo.name),
                'url', json_agg(repo.url)
            ) FROM repo WHERE id = event.repo_id) as repo
            FROM event
            WHERE date(create_at) = :date
            AND (payload)::text like '%{$searchInput->keyword}%'
SQL;

        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date->format('Y-m-d'),
            'keyword' => $searchInput->keyword,
        ]);

        $result = array_map(static function($item) {
            $item['repo'] = json_decode($item['repo'], true);

            return $item;
        }, $result);

        return $result;
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM event
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id
        ]);

        return (bool) $result;
    }
}
