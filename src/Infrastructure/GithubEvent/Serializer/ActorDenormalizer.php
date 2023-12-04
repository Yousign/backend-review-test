<?php

namespace App\Infrastructure\GithubEvent\Serializer;

use App\Domain\Entity\Actor;
use App\Domain\Entity\Event;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ActorDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (null === $data || empty($data['id'])) {
            return null;
        }
        return Actor::fromArray($data);
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
        return Actor::class === $type;
    }
}
