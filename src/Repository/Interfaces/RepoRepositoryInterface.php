<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\Repo;

/**
 * Interface for Repo repository operations.
 * 
 * This interface defines all read and write operations for Repo entities.
 * It provides a unified API for managing GitHub repositories.
 * 
 * @package App\Repository
 */
interface RepoRepositoryInterface
{
    /**
     * Find a repository by its ID.
     * 
     * @param mixed $id The repository ID to find
     * @param int|null $lockMode
     * @param int|null $lockVersion
     * @return Repo|null The repository if found, null otherwise
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Repo;
    
    /**
     * Find a repository by its ID or create a new one from array data.
     * 
     * @param array<string, mixed> $repoData The repository data array
     * @return Repo The existing or newly created repository
     */
    public function findOrCreate(array $repoData): Repo;
    
    /**
     * Save a repository entity.
     * 
     * @param Repo $repo The repository to save
     */
    public function save(Repo $repo): void;
} 