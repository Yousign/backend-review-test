<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enums\EventType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: '`event`')]
#[ORM\Index(columns: ['type'], name: 'idx_event_type')]
#[ORM\Index(columns: ['created_at'], name: 'idx_event_created_at')]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Assert\NotNull]
    #[Assert\Positive]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, enumType: EventType::class)]
    #[Assert\NotBlank]
    private EventType $type;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private int $count = 1;

    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id')]
    #[Assert\NotNull]
    #[Assert\Valid]
    private Actor $actor;

    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id')]
    #[Assert\NotNull]
    #[Assert\Valid]
    private Repo $repo;

    #[ORM\Column(type: 'json', nullable: false, options: ['jsonb' => true])]
    #[Assert\NotNull]
    #[Assert\Type('array')]
    private array $payload;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('now')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): EventType
    {
        return $this->type;
    }

    public function setType(EventType $type): self
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
}
