<?php

namespace App\Tests\Entity;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $actor = new Actor(1, 'user1', 'https://github.com/user1', 'https://avatar.com/user1.png');
        $repo = new Repo(2, 'repo/name', 'https://github.com/repo/name');
        $payload = ['key' => 'value'];
        $createdAt = new DateTimeImmutable('2025-06-10 14:00:00');
        $comment = 'Test comment';

        $event = new Event(100, EventType::COMMENT, $actor, $repo, $payload, $createdAt, $comment);

        $this->assertSame(100, $event->id());
        $this->assertSame(EventType::COMMENT, $event->type());
        $this->assertSame($actor, $event->actor());
        $this->assertSame($repo, $event->repo());
        $this->assertSame($payload, $event->payload());
        $this->assertSame($createdAt, $event->createAt());
        $this->assertSame($comment, $event->getComment());
    }

    public function testCommitEventCount(): void
    {
        $actor = new Actor(1, 'user1', 'https://github.com/user1', 'https://avatar.com/user1.png');
        $repo = new Repo(2, 'repo/name', 'https://github.com/repo/name');
        $payload = ['size' => 3];
        $createdAt = new DateTimeImmutable();

        $event = new Event(200, EventType::COMMIT, $actor, $repo, $payload, $createdAt, null);

        $reflection = new ReflectionClass($event);
        $property = $reflection->getProperty('count');

        $this->assertSame(3, $property->getValue($event));
    }
}
