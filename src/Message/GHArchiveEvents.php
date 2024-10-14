<?php

declare(strict_types=1);

namespace App\Message;

use App\Dto\GHArchiveEvent;

final class GHArchiveEvents
{
    /**
     * @param non-empty-array<GHArchiveEvent> $events
     */
    public function __construct(
        public readonly array $events,
    )
    {
    }
}
