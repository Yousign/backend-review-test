<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Repository\ReadEventRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

readonly class SearchController
{

    public function __construct(
        private ReadEventRepository $repository,
        private SerializerInterface  $serializer
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/search', name: 'api_search', methods: ['GET'])]
    public function searchCommits(Request $request): JsonResponse
    {
        assert($this->serializer instanceof Serializer, 'SerializerInterface should be an instance of Serializer');
        $searchInput = $this->serializer->denormalize($request->query->all(), SearchInput::class);

        assert($searchInput instanceof SearchInput, 'Denormalized object should be an instance of SearchInput');
        $countByType = $this->repository->countByType($searchInput);
        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($searchInput),
                'totalPullRequests' => $countByType['PR'] ?? 0,
                'totalCommits' => $countByType['COM'] ?? 0,
                'totalComments' => $countByType['MSG'] ?? 0,
            ],
            'data' => [
                'events' => $this->repository->getLatest($searchInput),
                'stats' => $this->repository->statsByTypePerHour($searchInput)
            ]
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
