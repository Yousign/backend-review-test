<?php

namespace App\Infrastructure\Event\Doctrine\Repository;

use App\Application\Dto\SearchInput;
use App\Domain\Entity\EventType;
use App\Domain\Repository\ReadEventRepositoryInterface;
use Doctrine\DBAL\Connection;

final class DbalReadEventRepository implements ReadEventRepositoryInterface
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
        AND payload::text LIKE :searchKeyword
SQL;

        return (int)$this->connection->fetchOne($sql, [
            'date' => $searchInput->date,
            'searchKeyword' => '%' . $searchInput->keyword . '%',
        ],
            [\Doctrine\DBAL\ParameterType::STRING, \Doctrine\DBAL\ParameterType::STRING]);
    }

    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<'SQL'
            SELECT type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            GROUP BY type
SQL;
        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date,
        ],
            [\Doctrine\DBAL\ParameterType::STRING]);
    }

    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT extract(hour from create_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload::text like :searchKeyword
            GROUP BY TYPE, EXTRACT(hour from create_at)
SQL;

        $stats = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date,
            'searchKeyword' => $searchInput->keyword,
        ],
            [\Doctrine\DBAL\ParameterType::STRING, \Doctrine\DBAL\ParameterType::STRING]);

        $data = array_fill(0, 24, ['commit' => 0, 'pullRequest' => 0, 'comment' => 0]);

        foreach ($stats as $stat) {
            $type = match ($stat['type']) {
                EventType::COMMIT => 'commit',
                EventType::PULL_REQUEST => 'pullRequest',
                EventType::COMMENT => 'comment',
            };
            $data[(int)$stat['hour']]['hour'] = $stat['hour'];
            $data[(int)$stat['hour']][$type] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, r.name
            FROM event
            INNER JOIN repo r ON event.repo_id = r.id
            WHERE date(create_at) = :date
            AND payload::text like :searchKeyword
SQL;

        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date,
            'searchKeyword' => $searchInput->keyword,
        ],
            [\Doctrine\DBAL\ParameterType::STRING, \Doctrine\DBAL\ParameterType::STRING]);

        $result = array_map(static function ($item) {
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

        return (bool)$result;
    }
}
