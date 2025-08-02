<?php

declare(strict_types=1);

namespace App\Managers;

use App\Entity\Event;
use App\Hydrator\ActorHydrator;
use App\Hydrator\EventHydrator;
use App\Hydrator\RepoHydrator;
use Doctrine\ORM\EntityManagerInterface;

class EventManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventHydrator $eventHydrator,
        private readonly ActorHydrator $actorHydrator,
        private readonly RepoHydrator $repoHydrator,
    ) {}

    public function saveEvent(array $array): void
    {
        $event = $this->entityManager->getRepository(Event::class)->find($array['id']);
        if ($event) {
            $this->entityManager->clear();

            return;
        }
        $event = $this->eventHydrator->setEventFromArray($array);

        $event->setActor($this->actorHydrator->setActorFromArray($array['actor']));

        $event->setRepo($this->repoHydrator->setRepoFromArray($array['repo']));

        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $this->entityManager->clear();

    }
}
