<?php

namespace App\Infrastructure\GithubEvent;

interface GithubEventArchiveDownloaderInterface
{
    public function download(string $date): iterable;
}
