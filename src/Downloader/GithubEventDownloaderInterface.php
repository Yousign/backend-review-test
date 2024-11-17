<?php

namespace App\Downloader;

interface GithubEventDownloaderInterface
{
    /**
     * Download and parse GitHub events for a specific date
     */
    public function downloadHourlyEvents(\DateTimeImmutable $period): array;
}
