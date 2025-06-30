<?php

namespace App\Service\Persister;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Contracts\Service\Attribute\Required;

use function array_diff;
use function array_fill;
use function array_map;
use function array_unique;
use function count;
use function implode;
use function in_array;

abstract class AbstractPersister
{
    private Connection $connection;

    #[Required]
    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @param string $idPath e.g. 'actor.id' or 'repo.id'
     * @param callable $extractFn function to extract row values (returns a list of values for a row)
     * @throws Exception
     */
    protected function persistBy(array $events, string $tableName, string $idPath, callable $extractFn): int
    {
        $ids = array_unique(
            array_map(fn (array $event) => $this->getNestedValue($event, $idPath), $events)
        );

        if (empty($ids)) {
            return 0;
        }

        $idsPlaceholder = implode(',', $ids);

        $existingIds = $this
            ->connection
            ->prepare("SELECT id FROM $tableName WHERE id IN ($idsPlaceholder)")
            ->executeQuery()
            ->fetchFirstColumn();

        $ids = array_diff($ids, $existingIds);
        $rowsToInsert = [];

        foreach ($events as $event) {
            $id = $this->getNestedValue($event, $idPath);
            if (in_array($id, $ids, true)) {
                $rowsToInsert[] = $extractFn($event);
                $ids = array_diff($ids, [$id]);
            }
        }

        if (count($rowsToInsert) === 0) {
            return 0;
        }

        $columnCount = count(
            reset($rowsToInsert)
        );

        $placeholders = $this->buildInsertPlaceholders(
            rowCount: count($rowsToInsert),
            columnCount: $columnCount
        );

        $params = array_merge(...$rowsToInsert);

        $this
            ->connection
            ->prepare("INSERT INTO $tableName VALUES $placeholders")
            ->executeStatement($params);

        return count($rowsToInsert);
    }

    private function buildInsertPlaceholders(int $rowCount, int $columnCount): string
    {
        $rowPlaceholders = [];

        for ($i = 0; $i < $rowCount; $i++) {
            $rowPlaceholders[] = '(' . implode(', ', array_fill(0, $columnCount, '?')) . ')';
        }

        return implode(', ', $rowPlaceholders);
    }

    private function getNestedValue(array $data, string $path): mixed
    {
        foreach (explode('.', $path) as $segment) {
            if (!isset($data[$segment])) {
                return null;
            }
            $data = $data[$segment];
        }

        return $data;
    }
}
