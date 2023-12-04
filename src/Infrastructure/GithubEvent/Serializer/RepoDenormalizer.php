<?php

namespace App\Infrastructure\GithubEvent\Serializer;

use App\Domain\Entity\Actor;
use App\Domain\Entity\Repo;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class RepoDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (null === $data) {
            return null;
        }
        return Repo::fromArray($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return Repo::class === $type && \App\Entity\Repo::class;
    }
}
