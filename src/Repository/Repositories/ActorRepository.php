<?php

declare(strict_types=1);

namespace App\Repository\Repositories;

use App\Entity\Actor;
use App\Repository\Interfaces\ActorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Actor entity operations.
 * 
 * This repository provides a unified interface for both reading and writing Actor entities.
 * It uses Doctrine ORM for type-safe database operations.
 * 
 * @package App\Repository
 */
class ActorRepository extends ServiceEntityRepository implements ActorRepositoryInterface
{
    /**
     * Constructor.
     * 
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actor::class);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Actor
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrCreate(array $actorData): Actor
    {
        $actorId = (int) $actorData['id'];
        
        $actor = $this->find($actorId);
        if ($actor !== null) {
            return $actor;
        }
        
        $actor = Actor::fromArray($actorData);
        $this->save($actor);
        
        return $actor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Actor $actor): void
    {
        $this->_em->persist($actor);
        $this->_em->flush();
    }
} 