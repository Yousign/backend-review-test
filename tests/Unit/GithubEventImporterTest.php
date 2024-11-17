<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Downloader\GithubEventDownloaderInterface;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Importer\GithubEventImporter;
use App\Repository\WriteEventRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class GithubEventImporterTest extends TestCase
{
    public function testImport(): void
    {
        $events = [
            new Event(
                1,
                EventType::COMMIT,
                new Actor(1, 'user1', 'http://github.com/user1', 'http://avatar.com/user1'),
                new Repo(1, 'test/repo1', 'test/repo1'),
                [],
                new \DateTimeImmutable('2024-01-15 00:00:00'),
                null
            ),
            new Event(
                2,
                EventType::PULL_REQUEST,
                new Actor(2, 'user2', 'http://github.com/user2', 'http://avatar.com/user2'),
                new Repo(2, 'test/repo2', 'test/repo2'),
                [],
                new \DateTimeImmutable('2024-01-15 00:00:00'),
                null
            ),
        ];

        $repository = $this->createMock(WriteEventRepository::class);
        $repository->expects($this->once())
            ->method('saveEvents')
            ->with($this->identicalTo($events));

        $downloader = $this->createMock(GithubEventDownloaderInterface::class);
        $downloader
            ->expects($this->exactly(24))
            ->method('downloadHourlyEvents')
            ->willReturnCallback(function (\DateTimeImmutable $period) use ($events) {
                return $period->format('H') === '00' ? $events : [];
            });

        $importer = new GithubEventImporter($downloader, $repository, new NullLogger());

        $date = \DateTimeImmutable::createFromFormat('Y-m-d-H', '2024-01-15-00');

        $importer->importerEvents($date->format('Y-m-d'));
    }
}
