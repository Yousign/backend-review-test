<?php

namespace App\Action;

use App\Application\Dto\SearchInput;
use App\Domain\Entity\EventType;
use App\Domain\Repository\ReadEventRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class SearchHandler
{
    public function __construct(
        private readonly ReadEventRepositoryInterface $repository,
        private readonly SerializerInterface          $serializer
    ) {
    }

    public function search(array $search): array
    {
        if (empty($search['date']) || empty($search['keyword'])) {
            throw new \InvalidArgumentException('Missing date or keyword to build SearchInput');
        }
        $searchInput = $this->serializer->denormalize($search, SearchInput::class);
        $countByType = $this->repository->countByType($searchInput);
        return [
            'meta' => [
                'totalEvents' => $this->repository->countAll($searchInput),
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $this->repository->getLatest($searchInput),
                'stats' => $this->repository->statsByTypePerHour($searchInput)
            ]
        ];
    }
}
