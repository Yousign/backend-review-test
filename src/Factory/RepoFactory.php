<?php

namespace App\Factory;

use App\Entity\Repo;
use InvalidArgumentException;

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
        if (!isset($data['id'], $data['name'], $data['url'])) {
            throw new InvalidArgumentException('Missing required Repo keys in data array');
        }
        return new Repo(
            (int) $data['id'],
            $data['name'],
            $data['url']
        );
    }
}
