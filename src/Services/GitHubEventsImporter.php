<?php

declare(strict_types=1);

namespace App\Services;

use App\Message\ImportGitHubEventsMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GitHubEventsImporter
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {}

    public function importEvents(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $this->logger->info('Starting GitHub events import for date range', [
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
        ]);

        $period = new \DatePeriod($startDate, new \DateInterval('PT1H'), $endDate->add(new \DateInterval('PT1H')));
        $messageCount = 0;

        foreach ($period as $date) {
            $dateHour = $date->format('Y-m-d-G');
            $this->logger->debug('Dispatching import message', [
                'date_hour' => $dateHour,
                'date' => $date->format('Y-m-d H:i:s'),
            ]);

            $this->messageBus->dispatch(
                new ImportGitHubEventsMessage($dateHour),
            );
            ++$messageCount;
        }

        $this->logger->info('GitHub events import messages dispatched', [
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'total_messages' => $messageCount,
        ]);
    }
}
