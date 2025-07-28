<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\EventType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: '`event`',
    indexes: [new ORM\Index(columns: ['type'], name: 'IDX_EVENT_TYPE')],
)]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $id;

    #[ORM\Column(type: 'EventType', nullable: false)]
    private string $type;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $count = 1;

    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id')]
    private Actor $actor;

    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id')]
    private Repo $repo;

    #[ORM\Column(type: 'json', nullable: false, options: ['jsonb' => true])]
    private array $payload;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    public function __construct(int $id, string $type, array $payload, \DateTimeImmutable $createdAt, ?string $comment)
    {
        $this->id = $id;
        EventType::assertValidChoice($type);
        $this->type = $type;
        $this->payload = $payload;
        $this->createdAt = $createdAt;
        $this->comment = $comment;

        if (EventType::COMMIT === $type) {
            $this->count = $payload['size'] ?? 1;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        if (EventType::COMMIT === $this->type) {
            $this->count = $this->payload['size'] ?? 1;
        } else {
            $this->count = $count;
        }

        return $this;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function setActor(Actor $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getRepo(): Repo
    {
        return $this->repo;
    }

    public function setRepo(Repo $repo): self
    {
        $this->repo = $repo;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public static function fromArray(array $array)
    {
        return new self(
            (int) $array['id'],
            $array['type'],
            $array['payload'] ?? [],
            new \DateTimeImmutable($array['created_at']),
            $array['comment'] ?? null,
        );

    }
}
