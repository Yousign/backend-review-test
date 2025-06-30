<?php

namespace App\Dto;

final readonly class RepoInput
{
    public function __construct(
        public int $id,
        public string $name,
        public string $url
    ) {
    }
}
