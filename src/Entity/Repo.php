<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="repo")
 */
class Repo
{
    public function __construct(
        /**
         * @ORM\Id
         *
         * @ORM\Column(type="bigint")
         *
         * @ORM\GeneratedValue(strategy="NONE")
         */
        private int $id,
        /**
         * @ORM\Column(type="string")
         */
        public string $name,
        /**
         * @ORM\Column(type="string")
         */
        public string $url
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }

    /**
     * @param array{id: int|string, name: string, url: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['name'],
            $data['url']
        );
    }
}
