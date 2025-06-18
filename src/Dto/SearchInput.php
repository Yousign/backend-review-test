<?php

namespace App\Dto;

use DateTimeImmutable;

class SearchInput
{
    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $date;

    /**
     * @var string
     */
    public string $keyword;
}
