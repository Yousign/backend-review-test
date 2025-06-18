<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Factory\ActorFactory;
use App\Factory\RepoFactory;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture
{
    public const int EVENT_1_ID = 1;
    public const string ACTOR_1_ID = "1";
    public const string REPO_1_ID = "1";

    public function load(ObjectManager $manager): void
    {
        $actor = ActorFactory::fromArray([
            'id' => self::ACTOR_1_ID,
            'login' => 'jdoe',
            'url' => 'https://api.github.com/users/jdoe',
            'avatar_url' => 'https://avatars.githubusercontent.com/u/1?',
        ]);

        $repo = RepoFactory::fromArray([
            'id' => self::REPO_1_ID,
            'name' =>  'yousign/test',
            'url' => 'https://api.github.com/repos/yousign/backend-test',
        ]);

        $event = new Event(
            self::EVENT_1_ID,
            EventType::COMMENT,
            $actor,
            $repo,
            [],
            new DateTimeImmutable('2024-01-01T12:00:00+00:00'),
            'Test comment initiated by fixture'
        );

        $manager->persist($event);
        $manager->flush();
    }
}
