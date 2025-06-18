<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="`event`",
 *    indexes={@ORM\Index(name="IDX_EVENT_TYPE", columns={"type"})}
 * )
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private int $id;

    /**
     * @ORM\Column(type="EventType", nullable=false)
     */
    private string $type;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $count = 1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", cascade={"persist"})
     * @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     */
    private Actor $actor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Repo", cascade={"persist"})
     * @ORM\JoinColumn(name="repo_id", referencedColumnName="id")
     */
    private Repo $repo;

    /**
     * @ORM\Column(type="json", nullable=false, options={"jsonb": true})
     * @var array<string, mixed>
     */
    private array $payload;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private DateTimeImmutable $createAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment;

    /**
     * Event constructor.
     *
     * @param int $id
     * @param string $type
     * @param Actor $actor
     * @param Repo $repo
     * @param array<string, mixed> $payload
     * @param DateTimeImmutable $createAt
     * @param string|null $comment
     */
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

        if ($type === EventType::COMMIT) {
            if(!is_int($payload['size'])) {
                $this->count = 1;
            }
            else{
                $this->count = $payload['size'];
            }
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


    /**
     * Get the payload of the event.
     *
     * @return array<string, mixed>
     */
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

    public function count(): int
    {
        return $this->count;
    }
}
