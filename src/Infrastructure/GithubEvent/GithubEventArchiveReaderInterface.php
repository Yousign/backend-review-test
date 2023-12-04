<?php

namespace App\Infrastructure\GithubEvent;

interface GithubEventArchiveReaderInterface
{
    public function read($filename);
}
