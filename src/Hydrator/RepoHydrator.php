<?php

namespace App\Hydrator;

use App\Entity\Repo;
use Doctrine\ORM\EntityManagerInterface;

class RepoHydrator
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function setRepoFromArray(array $array): Repo
    {
        $existingRepo = $this->entityManager->getRepository(Repo::class)->find((int) $array['id']);

        if ($existingRepo) {
            return $existingRepo;
        }
        $repo = new Repo();
        $repo->setId((int) $array['id'])
            ->setName($array['name'])
            ->setUrl($array['url']);
        $this->entityManager->persist($repo);

        return $repo;
    }
}
