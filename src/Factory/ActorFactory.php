<?php

namespace App\Factory;

use App\Entity\Actor;
use InvalidArgumentException;

class ActorFactory
{
    public static function create(int $id, string $login, string $url, string $avatarUrl): Actor
    {
        return new Actor($id, $login, $url, $avatarUrl);
    }

    /**
     * @param array<string, string> $data
     * @return Actor
     */
    public static function fromArray(array $data): Actor
    {
        if (!isset($data['id'], $data['login'], $data['url'], $data['avatar_url'])) {
            throw new InvalidArgumentException('Missing required Actor keys in data array');
        }
        return new Actor(
            (int) $data['id'],
            $data['login'],
            $data['url'],
            $data['avatar_url']
        );
    }
}
