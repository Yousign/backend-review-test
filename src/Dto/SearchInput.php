<?php

declare(strict_types=1);

namespace App\Dto;

class SearchInput
{
    /**
     * @var \DateTimeImmutable
     */
    public $date;

    /**
     * @var string
     */
    public $keyword;
}
