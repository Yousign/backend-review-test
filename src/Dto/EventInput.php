<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class EventInput
{
    #[Assert\Length(min: 20, minMessage: 'This value is too short. It should have 20 characters or more.')]
    public ?string $comment;

    public function __construct(?string $comment) {
        $this->comment = $comment;
    }
}
