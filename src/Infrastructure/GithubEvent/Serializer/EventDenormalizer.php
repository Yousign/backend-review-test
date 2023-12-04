<?php

namespace App\Infrastructure\GithubEvent\Serializer;

use App\Domain\Entity\Event;
use App\Domain\Entity\EventType;
use App\Domain\Entity\Actor;
use App\Domain\Entity\Repo;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventDenormalizer implements DenormalizerInterface
{
    protected readonly array $allowedEventTypes;

    public function __construct()
    {
        $this->allowedEventTypes = array_keys(EventType::ALLOWED_EVENT_TYPES);
    }
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (null === $data) {
            return null;
        }

        if (! in_array($data['type'], $this->allowedEventTypes, true)) {
            return null;
        }
        if (empty($data['actor']['id']) || empty($data['repo']['id'])) {
            return null;
        }
        return Event::fromArray([
            'id'    => $data['id'] ?? null,
            'type'  => EventType::ALLOWED_EVENT_TYPES[$data['type']],
            'actor' => Actor::fromArray($data['actor']),
            'repo'  => Repo::fromArray($data['repo']),
            'payload' => $data['payload'],
            'create_at' => new \DateTimeImmutable($data['created_at']),
            'comment' => $data['comment'] ?? null,
        ]);
    }


    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return Event::class === $type;
    }
}
