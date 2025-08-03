<?php

namespace App\Tests\Func\Managers;

use App\Enums\EventType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Managers\EventManager;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class EventManagerFunctionalTest extends KernelTestCase
{
    private EventManager $eventManager;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->eventManager = self::getContainer()->get(EventManager::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testSaveNewEvent(): void
    {
        $eventData = [
            'id' => 999999,
            'type' => 'PushEvent',
            'actor' => [
                'id' => 123,
                'login' => 'testuser',
                'display_login' => 'TestUser',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/testuser',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/123?',
            ],
            'repo' => [
                'id' => 456,
                'name' => 'test/repo',
                'url' => 'https://api.github.com/repos/test/repo',
            ],
            'payload' => [
                'comment' => [
                    'body' => 'this is a test comment',
                ],
            ],
            'created_at' => '2024-01-01T00:00:00Z',
        ];

        $this->eventManager->saveEvent($eventData);

        $savedEvent = $this->entityManager->getRepository(Event::class)->find($eventData['id']);

        self::assertNotNull($savedEvent);
        self::assertEquals(EventType::from($eventData['type']), $savedEvent->getType());
        self::assertEquals($eventData['actor']['login'], $savedEvent->getActor()->getLogin());
        self::assertEquals($eventData['repo']['name'], $savedEvent->getRepo()->getName());
    }

    public function testSaveExistingEventDoesNotDuplicate(): void
    {
        $eventData = [
            'id' => 888888,
            'type' => 'PushEvent',
            'actor' => [
                'id' => 321,
                'login' => 'existinguser',
                'display_login' => 'ExistingUser',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/existinguser',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/321?',
            ],
            'repo' => [
                'id' => 654,
                'name' => 'existing/repo',
                'url' => 'https://api.github.com/repos/existing/repo',
            ],
            'payload' => [
                'comment' => [
                    'body' => 'this is a test comment',
                ],
            ],
            'created_at' => '2024-01-01T00:00:00Z',
        ];

        $this->eventManager->saveEvent($eventData);

        $this->eventManager->saveEvent($eventData);

        $savedEvent = $this->entityManager->getRepository(Event::class)->find($eventData['id']);

        self::assertNotNull($savedEvent);
        self::assertEquals($eventData['actor']['login'], $savedEvent->getActor()->getLogin());
        self::assertEquals($eventData['repo']['name'], $savedEvent->getRepo()->getName());
    }
}
