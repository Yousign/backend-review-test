<?php

namespace App\Repository;

use App\Dto\SearchInput;
use Doctrine\DBAL\Exception;

interface ReadEventRepository
{
    /**
     * @throws Exception
     */
    public function countAll(SearchInput $searchInput): int;

    /**
     * @return array<string, int>
     * @throws Exception
 */
    public function countByType(SearchInput $searchInput): array;

    /**
     * @return array<int, array<string, int>>
     * @throws Exception
    */
    public function statsByTypePerHour(SearchInput $searchInput): array;
    /**
     * @return array<int, array<string, int|string>>
     * @throws Exception
     */
    public function getLatest(SearchInput $searchInput): array;
    public function exist(int $id): bool;
}
