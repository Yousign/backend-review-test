<?php

namespace App\Infrastructure\GithubEvent;

final class GithubEventArchiveDownloader implements GithubEventArchiveDownloaderInterface
{
    public function __construct(
        private readonly GithubEventApiClientInterface $client,
        private readonly UriBuilderInterface $uriBuilder
    ) {
    }
    public function download(string $date): iterable
    {
        return $this->client->retrieve($this->uriBuilder->build($date));
    }
}
