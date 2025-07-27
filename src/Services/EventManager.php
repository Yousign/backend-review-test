<?php

namespace App\Services;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use Doctrine\ORM\EntityManagerInterface;

class EventManager
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function saveEvent(array $array): void
    {
        $event = $this->entityManager->getRepository(Event::class)->find($array['id'] ?? null)
            ?? Event::fromArray($array);
        $actor = $this->entityManager->getRepository(Actor::class)->find($array['actor']['id'] ?? null)
            ?? Actor::fromArray($array['actor']);
        $repo = $this->entityManager->getRepository(Repo::class)->find($array['repo']['id'] ?? null)
            ?? Repo::fromArray($array['repo']);
        $event->setActor($actor);
        $event->setRepo($repo);
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
