<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventInput
{
    public function __construct(
        #[Assert\Length(min: 20)]
        public ?string $comment = null
    ) {
    }
}
