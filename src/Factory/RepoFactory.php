<?php

namespace App\Factory;

use App\Entity\Repo;

class RepoFactory
{
    public static function create(int $id, string $name, string $url): Repo
    {
        return new Repo($id, $name, $url);
    }

    /**
     * @param array<string, string> $data
     * @return Repo
     */
    public static function fromArray(array $data): Repo
    {
        return new Repo(
            (int) $data['id'],
            $data['name'],
            $data['url']
        );
    }
}
