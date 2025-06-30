<?php

namespace App\Dto;

class ActorInput
{
    public function __construct(
        public int $id,
        public string $login,
        public string $url,
        public string $avatarUrl,
    ) {
    }
}
