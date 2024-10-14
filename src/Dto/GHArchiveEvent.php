<?php

declare(strict_types=1);

namespace App\Dto;

final class GHArchiveEvent
{
    public string $id;
    public string $type;
    public GHArchiveActor $actor;
    public GHArchiveRepo $repo;
    public array $payload;
    public bool $public;
    public string $createdAt;
}
