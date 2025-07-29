<?php

namespace App\Service\Interfaces;

use Symfony\Component\Console\Style\SymfonyStyle;

interface GithubArchiveInterface
{
    /**
     * Dry run import from GH Archive. 
     * 
     * @internal This method won't save any data in database, only purpose is for development purposes.
     * 
     * @param SymfonyStyle $io
     * @param int $year
     * @param int|null $month
     * @param int|null $day
     * @param int|null $hour
     * @param string $keyword
     * @return int
     */
    public function dryRunImportFromGHArchive(SymfonyStyle $io, int $year, ?int $month, ?int $day, ?int $hour, string $keyword): int;

    /**
     * Import events from GH Archive.
     * @param string $startDate
     * @param string $endDate
     * @param int $hour
     * @param string $keyword
     * @return int
     */
    public function importEventsFromGHArchive(string $startDate, string $endDate, int $hour, string $keyword): int;
}