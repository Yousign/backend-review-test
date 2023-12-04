<?php

namespace App\Domain\Entity;

class Event
{

    private int $id;

    private string $type;

    private int $count = 1;

    private Actor $actor;

    private Repo $repo;

    private array $payload;

    private \DateTimeImmutable $createAt;

    private ?string $comment;

    public function __construct(int $id, string $type, Actor $actor, Repo $repo, array $payload, \DateTimeImmutable $createAt, ?string $comment)
    {
        $this->id = $id;
        EventType::assertValidChoice($type);
        $this->type = $type;
        $this->actor = $actor;
        $this->repo = $repo;
        $this->payload = $payload;
        $this->createAt = $createAt;
        $this->comment = $comment;

        if ($type === EventType::COMMIT) {
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

    public function count(): int
    {
         return $this->count;
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
        return $this->createAt;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }


    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['type'],
            $data['actor'],
            $data['repo'],
            $data['payload'],
            $data['create_at'],
            $data['comment'],
        );
    }
}
