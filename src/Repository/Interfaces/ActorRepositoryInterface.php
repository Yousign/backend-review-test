<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\Actor;

/**
 * Interface for Actor repository operations.
 * 
 * This interface defines all read and write operations for Actor entities.
 * It provides a unified API for managing GitHub actors.
 * 
 * @package App\Repository
 */
interface ActorRepositoryInterface
{
    /**
     * Find an actor by its ID.
     * 
     * @param mixed $id The actor ID to find
     * @param int|null $lockMode
     * @param int|null $lockVersion
     * @return Actor|null The actor if found, null otherwise
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Actor;
    
    /**
     * Find an actor by its ID or create a new one from array data.
     * 
     * @param array<string, mixed> $actorData The actor data array
     * @return Actor The existing or newly created actor
     */
    public function findOrCreate(array $actorData): Actor;
    
    /**
     * Save an actor entity.
     * 
     * @param Actor $actor The actor to save
     */
    public function save(Actor $actor): void;
} 