<?php

namespace App\Service\Services;

use App\Service\Interfaces\GithubArchiveInterface;

class GithubArchiveService implements GithubArchiveInterface
{
    public function dryRunImportFromGHArchive(string $date, int $hour, string $keyword): int
    {
        // TODO: Implement dryRunImportFromGHArchive() method.
        return 0;
    }

    public function importEventsFromGHArchive(string $date, int $hour, string $keyword): int
    {
        // TODO: Implement importEventsFromGHArchive() method.
        return 0;
    }
}