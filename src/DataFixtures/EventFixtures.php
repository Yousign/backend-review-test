<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\DBAL\Types\EventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture
{
    public const int EVENT_1_ID = 1;
    public const int ACTOR_1_ID = 1;
    public const int REPO_1_ID = 1;

    public function load(ObjectManager $manager): void
    {
        $actor = new Actor();
        $actor->setId(self::ACTOR_1_ID)
            ->setLogin('jdoe')
            ->setUrl('https://api.github.com/users/jdoe')
            ->setAvatarUrl('https://avatars.githubusercontent.com/u/1?');

        $repo = new Repo();
        $repo->setId(self::REPO_1_ID)
            ->setName('yousign/test')
            ->setUrl('https://api.github.com/repos/yousign/backend-test');

        $event = new Event();
        $event->setId(self::EVENT_1_ID)
            ->setType(EventType::COMMENT)
            ->setPayload([])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setComment('Test comment initiate by fixture ')
            ->setActor($actor)
            ->setRepo($repo);

        $manager->persist($event);
        $manager->flush();
    }
}
