<?php

namespace App\Services;

use App\Message\ImportGitHubEventsMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class GitHubEventsImporter
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function importEvents(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $period = new \DatePeriod($startDate, new \DateInterval('PT1H'), $endDate->add(new \DateInterval('PT1H')));
        foreach ($period as $date) {
            $this->messageBus->dispatch(
                new ImportGitHubEventsMessage(
                    $date->format('Y-m-d-G'),
                ),
            );
        }
    }
}
