<?php

namespace App\Factory;

use App\Entity\Actor;

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
        return new Actor(
            (int) $data['id'],
            $data['login'],
            $data['url'],
            $data['avatar_url']
        );
    }
}
