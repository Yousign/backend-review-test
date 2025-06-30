<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SearchInput
{
    public function __construct(
        #[Assert\Date]
        #[Assert\NotBlank]
        public ?string $date,

        public ?string $keyword,

        #[Assert\GreaterThanOrEqual(1)]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 100)]
        public int $itemsPerPage = 10,
    ) {
    }
}
