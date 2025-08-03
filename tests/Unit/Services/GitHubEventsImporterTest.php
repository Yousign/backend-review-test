<?php

namespace App\Tests\Unit\Services;

use App\Message\ImportGitHubEventsMessage;
use App\Services\GitHubEventsImporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class GitHubEventsImporterTest extends TestCase
{
    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var MessageBusInterface&MockObject
     */
    private MessageBusInterface $messageBus;
    private GitHubEventsImporter $gitHubEventsImporter;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->gitHubEventsImporter = new GitHubEventsImporter($this->messageBus, $this->logger);
    }

    public function testImportEvents(): void
    {
        $startDate = new \DateTimeImmutable('2023-10-01 14:00:00');
        $endDate = new \DateTimeImmutable('2023-10-01 15:00:00');

        $this->messageBus->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($message) {
                self::assertInstanceOf(ImportGitHubEventsMessage::class, $message);

                return new Envelope($message);
            });
        $debugCalls = [];

        $this->logger
            ->method('debug')
            ->willReturnCallback(function ($message, $context) use (&$debugCalls) {
                $debugCalls[] = ['message' => $message, 'context' => $context];
            });
        $this->gitHubEventsImporter->importEvents($startDate, $endDate);

        $expectedDates = [
            '2023-10-01 14:00:00',
            '2023-10-01 15:00:00',
        ];

        foreach ($expectedDates as $i => $dateStr) {
            $date = new \DateTimeImmutable($dateStr);
            self::assertEquals('Dispatching import message', $debugCalls[$i]['message']);
            self::assertEquals([
                'date_hour' => $date->format('Y-m-d-G'),
                'date' => $date->format('Y-m-d H:i:s'),
            ], $debugCalls[$i]['context']);
        }

    }
}
