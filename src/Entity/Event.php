<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['type'], name: 'IDX_EVENT_TYPE')]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: 'EventType', nullable: false)]
    private string $type;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $count = 1;

    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    private Actor $actor;

    #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    private Repo $repo;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $payload;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $createAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    public function __construct(int $id, string $type, Actor $actor, Repo $repo, array $payload, DateTimeImmutable $createAt, ?string $comment)
    {
        $this->id = $id;
        EventType::assertValidChoice($type);
        $this->type = $type;
        $this->actor = $actor;
        $this->repo = $repo;
        $this->payload = $payload;
        $this->createAt = $createAt;
        $this->comment = $comment;

        if (EventType::COMMIT === $type) {
            $this->count = $payload['size'] ?? 1;
        }
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

    public function createAt(): DateTimeImmutable
    {
        return $this->createAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
