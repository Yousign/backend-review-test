<?php

namespace App\Dto;

use DateTimeImmutable;

class SearchInput
{
    public DateTimeImmutable $date;

    public string $keyword;
}
