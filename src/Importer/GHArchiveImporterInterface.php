<?php

namespace App\Importer;

interface GHArchiveImporterInterface
{
    /**
     * Dispatches messages to import GH Archive events for a given period of time.
     *
     * @return int The number of GHArchiveEvent read
     */
    public function import(\DateTimeImmutable $from, ?\DateTimeImmutable $to = null): int;
}
