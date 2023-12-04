<?php

namespace App\Application\Serializer;

use App\Application\Dto\SearchInput;
use App\Domain\Entity\Actor;
use App\Domain\Entity\Event;
use App\Domain\Entity\EventType;
use App\Domain\Entity\Repo;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SearchInputDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (null === $data) {
            return null;
        }
        $searchInput = new SearchInput();
        $searchInput->date = $data['date'];
        $searchInput->keyword = $data['keyword'];

        return $searchInput;
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
        return SearchInput::class === $type;
    }
}
