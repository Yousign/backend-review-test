<?php

namespace App\Hydrator;

use App\Entity\Actor;
use Doctrine\ORM\EntityManagerInterface;

class ActorHydrator
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function setActorFromArray(array $array): Actor
    {
        $existingActor = $this->entityManager->getRepository(Actor::class)->find((int) $array['id']);

        if ($existingActor) {
            return $existingActor;
        }

        $actor = new Actor();
        $actor->setId((int) $array['id'])
            ->setLogin($array['login'])
            ->setUrl($array['url'])
            ->setAvatarUrl($array['avatar_url']);

        $this->entityManager->persist($actor);

        return $actor;
    }
}
