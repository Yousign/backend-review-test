<?php

namespace App\Service\Interfaces;

interface GithubArchiveInterface
{
    /**
     * Dry run import from GH Archive. 
     * 
     * @internal This method won't save any data in database, only purpose is for development purposes.
     * 
     * @param string $date
     * @param int $hour
     * @param string $keyword
     * @return int
     */
    public function dryRunImportFromGHArchive(string $date, int $hour, string $keyword): int;

    /**
     * Import events from GH Archive.
     * @param string $date
     * @param int $hour
     * @param string $keyword
     * @return int
     */
    public function importEventsFromGHArchive(string $date, int $hour, string $keyword): int;
}