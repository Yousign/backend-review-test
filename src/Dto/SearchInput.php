<?php

namespace App\Dto;

use DateTimeImmutable;

class SearchInput
{
    /**
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * @var string
     */
    public $keyword;
}
