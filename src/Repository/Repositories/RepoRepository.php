<?php

declare(strict_types=1);

namespace App\Repository\Repositories;

use App\Entity\Repo;
use App\Repository\Interfaces\RepoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Repo entity operations.
 * 
 * This repository provides a unified interface for both reading and writing Repo entities.
 * It uses Doctrine ORM for type-safe database operations.
 * 
 * @package App\Repository
 */
class RepoRepository extends ServiceEntityRepository implements RepoRepositoryInterface
{
    /**
     * Constructor.
     * 
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repo::class);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Repo
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrCreate(array $repoData): Repo
    {
        $repoId = (int) $repoData['id'];
        
        $repo = $this->find($repoId);
        if ($repo !== null) {
            return $repo;
        }
        
        $repo = Repo::fromArray($repoData);
        $this->save($repo);
        
        return $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Repo $repo): void
    {
        $this->_em->persist($repo);
        $this->_em->flush();
    }
} 