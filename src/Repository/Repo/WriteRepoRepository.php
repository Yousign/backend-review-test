<?php

namespace App\Repository\Repo;

use App\Dto\RepoInput;

interface WriteRepoRepository
{
    public function insert(RepoInput $repoInput): void;
}
