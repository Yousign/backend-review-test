<?php

namespace App\Dto;

use DateTimeImmutable;

readonly class SearchInput
{
    public function __construct(
        public ?DateTimeImmutable $date = null,
        public string $keyword = ''
    ) {}
}
