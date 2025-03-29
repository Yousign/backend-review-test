<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'actor')]
class Actor
{
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Id]
    public string $id;

    #[ORM\Column]
    public string $login;

    #[ORM\Column]
    public string $url;

    #[ORM\Column]
    public string $avatarUrl;

    public function __construct(int $id, string $login, string $url, string $avatarUrl)
    {
        $this->id = (string) $id;
        $this->login = $login;
        $this->url = $url;
        $this->avatarUrl = $avatarUrl;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function avatarUrl(): string
    {
        return $this->avatarUrl;
    }

    /**
     * @param array{id: int, login: string, url: string, avatar_url: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['login'],
            $data['url'],
            $data['avatar_url']
        );
    }
}
