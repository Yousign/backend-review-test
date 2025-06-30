<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`event`')]
#[ORM\Index(columns: ['type'], name: 'IDX_EVENT_TYPE')]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private readonly int $id;

    #[ORM\Column(type: 'EventType')]
    private readonly string $type;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $count = 1;

    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id')]
    private readonly Actor $actor;

    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id')]
    private readonly Repo $repo;

    #[ORM\Column(type: 'json', nullable: false, options: ['jsonb' => false])]
    private readonly array $payload;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private readonly ?string $comment;

    public function __construct(int $id, string $type, Actor $actor, Repo $repo, array $payload, \DateTimeImmutable $createAt, ?string $comment)
    {
        $this->id = $id;
        $this->type = $type;
        $this->actor = $actor;
        $this->repo = $repo;
        $this->payload = $payload;
        $this->createdAt = $createAt;
        $this->comment = $comment;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function actor(): Actor
    {
        return $this->actor;
    }

    public function repo(): Repo
    {
        return $this->repo;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function createAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function count(): int
    {
        return $this->count;
    }
}
