<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class EventInput
{
    public function __construct(
        /**
         * @Assert\Length(min=20)
         */
        public ?string $comment
    ) {
    }
}
