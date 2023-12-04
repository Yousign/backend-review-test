<?php

namespace App\Infrastructure\GithubEvent;

interface UriBuilderInterface
{
    public function build(string $date): string;
}
