<?php

namespace App\Application\Controller;

use App\Action\SearchHandler;
use App\Domain\Repository\ReadEventRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class SearchController
{
    private ReadEventRepositoryInterface $repository;
    private SerializerInterface $serializer;

    public function __construct(
        private readonly SearchHandler $searchHandler
    ) {
    }

    /**
     * @Route(path="/api/search", name="api_search", methods={"GET"})
     */
    public function searchCommits(Request $request): JsonResponse
    {
        try {
            $response = $this->searchHandler->search($request->query->all());
            return new JsonResponse($response);
        }
        catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
