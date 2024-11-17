<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Command\ImportGitHubEventsCommand;
use App\Downloader\GithubEventDownloaderInterface;
use App\Entity\Event;
use App\Importer\GithubEventImporter;
use App\Repository\WriteEventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \App\Command\ImportGitHubEventsCommand
 */
final class ImportGitHubEventsCommandTest extends TestCase
{
    private MockObject&GithubEventDownloaderInterface $downloader;
    private MockObject&WriteEventRepository $writeEventRepository;
    private MockObject&LoggerInterface $logger;
    private GithubEventImporter $importer;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->downloader = $this->createMock(GithubEventDownloaderInterface::class);
        $this->writeEventRepository = $this->createMock(WriteEventRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->importer = new GithubEventImporter(
            $this->downloader,
            $this->writeEventRepository,
            $this->logger
        );

        $command = new ImportGitHubEventsCommand($this->importer);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccessfully(): void
    {
        $today = new \DateTimeImmutable();
        $formattedDate = $today->format('Y-m-d');

        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $this->downloader
            ->expects($this->exactly(24))
            ->method('downloadHourlyEvents')
            ->willReturn($events);

        $this->writeEventRepository
            ->expects($this->exactly(24))
            ->method('saveEvents')
            ->with($this->equalTo($events));

        $this->logger
            ->expects($this->exactly(24))
            ->method('info')
            ->with($this->stringContains('Importation des événements pour l\'heure'));

        $this->commandTester->execute([
            'date' => $formattedDate,
        ]);

        $this->commandTester->assertCommandIsSuccessful();
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Importation terminée.', $display);
    }

    public function testExecuteWithDownloaderError(): void
    {
        $today = new \DateTimeImmutable();
        $formattedDate = $today->format('Y-m-d');

        $this->downloader
            ->expects($this->atLeastOnce())
            ->method('downloadHourlyEvents')
            ->willThrowException(new \Exception('Download failed'));

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('error')
            ->with(
                $this->stringContains('Erreur lors de l\'importation des événements'),
                $this->arrayHasKey('exception')
            );

        $this->commandTester->execute([
            'date' => $formattedDate,
        ]);

        $this->commandTester->assertCommandIsSuccessful();
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Importation terminée.', $display);
    }

    public function testExecuteWithEmptyEvents(): void
    {
        $today = new \DateTimeImmutable();
        $formattedDate = $today->format('Y-m-d');

        $this->downloader
            ->expects($this->exactly(24))
            ->method('downloadHourlyEvents')
            ->willReturn([]);

        $this->writeEventRepository
            ->expects($this->never())
            ->method('saveEvents');

        $this->commandTester->execute([
            'date' => $formattedDate,
        ]);

        $this->commandTester->assertCommandIsSuccessful();
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Importation terminée.', $display);
    }
}
