<?php

namespace App\Infrastructure\GithubEvent;

final class UriBuilder implements UriBuilderInterface
{
    public function build(string $date): string
    {
        return sprintf('https://data.gharchive.org/%s.json.gz', $date);
    }
}
