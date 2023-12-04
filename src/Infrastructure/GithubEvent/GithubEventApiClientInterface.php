<?php

namespace App\Infrastructure\GithubEvent;

interface GithubEventApiClientInterface
{
    public function retrieve(string $url): iterable;
}
