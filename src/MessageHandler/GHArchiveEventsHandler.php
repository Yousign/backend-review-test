<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GHArchiveEvents;
use App\Repository\WriteEventRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GHArchiveEventsHandler
{
    public function __construct(
        private readonly WriteEventRepository $repository,
    ) {}

    /**
     * @throws \Throwable
     * @throws Exception
     */
    public function __invoke(GHArchiveEvents $events): void
    {
        $this->repository->insertGHArchiveEvents($events->events);
    }
}
