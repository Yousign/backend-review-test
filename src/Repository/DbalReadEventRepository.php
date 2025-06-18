<?php

namespace App\Repository;

use App\Dto\SearchInput;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DbalReadEventRepository implements ReadEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
            SELECT sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload::text like :keyword
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'date' => $searchInput->date?->format('Y-m-d'),
            'keyword' => '%' . $searchInput->keyword . '%'
        ]);
        if($result === false || $result === null) {
            return 0;
        }
        if(!is_int($result)) {
            throw new Exception('Invalid result from count query');
        }
        return $result;
    }

    /**
     * @return array<string, mixed>
     * @throws Exception
     */
    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<'SQL'
            SELECT type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload::text like :keyword
            GROUP BY type
        SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date?->format('Y-m-d'),
            'keyword' => '%' . $searchInput->keyword . '%'
        ]);
    }

    /**
     * @return array<int, array<string, float|int|string>>
     * @throws Exception
     */
    public function statsByTypePerHour(SearchInput $searchInput): array
    {

        $sql = <<<SQL
                SELECT extract(hour from create_at) as hour, type, sum(count) as count
                FROM event
                WHERE date(create_at) = :date
                AND payload::text like :keyword
                GROUP BY TYPE, EXTRACT(hour from create_at)
            SQL;

        $stats = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date?->format('Y-m-d'),
            'keyword' => '%' . $searchInput->keyword . '%'
        ]);


        $data = array_fill(0, 24, ['COM' => 0, 'PR' => 0, 'MSG' => 0]);

        foreach ($stats as $stat) {
            if (!isset($stat['hour'], $stat['type'], $stat['count']) || !is_numeric($stat['hour']) || !is_string($stat['type']) || !is_numeric($stat['count'])) {
                continue; // Skip if any of the required fields are missing
            }
            $data[intval($stat['hour'])][$stat['type']] = $stat['count'];
        }

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
                SELECT type, r as repo
                 FROM event
                 LEFT JOIN public.repo r on r.id = event.repo_id
                WHERE date(create_at) = :date
                AND payload::text like :keyword
            SQL;

        return $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date?->format('Y-m-d'),
            'keyword' => '%' . $searchInput->keyword . '%'
        ]);
    }

    /**
     * @throws Exception
     */
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
