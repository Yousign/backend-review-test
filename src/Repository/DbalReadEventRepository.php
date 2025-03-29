<?php

namespace App\Repository;

use App\Dto\SearchInput;
use Doctrine\DBAL\Connection;

readonly class DbalReadEventRepository implements ReadEventRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
        SELECT sum(count) as count
        FROM event
        WHERE date(create_at) = :date
        AND payload like %{$searchInput->keyword}%
SQL;

        return (int) $this->connection->fetchOne($sql, [
            'date' => $searchInput->date,
        ]);
    }

    /**
     * @return array<string, string|int>
     */
    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<'SQL'
            SELECT type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
            GROUP BY type
SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date,
        ]);
    }

    /**
     * @return array<int, array<string, int>>
     */
    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT extract(hour from create_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
            GROUP BY TYPE, EXTRACT(hour from create_at)
SQL;

        $stats = $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date,
        ]);

        $data = array_fill(0, 24, ['commit' => 0, 'pullRequest' => 0, 'comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = $stat['count'];
        }

        return $data;
    }

    /**
     * @return array<int, array<string, array<string, string>>>
     */
    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, repo
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
SQL;

        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date,
            'keyword' => $searchInput->keyword,
        ]);

        return array_map(static function ($item) {
            $item['repo'] = json_decode((string) $item['repo'], true);

            return $item;
        }, $result);
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
