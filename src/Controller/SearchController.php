<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Service\Interfaces\EventServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SearchController
{
    public function __construct(
        private readonly EventServiceInterface $eventService,
        private readonly DenormalizerInterface $serializer
    ) {
    }

    /**
     * @Route(path="/api/search", name="api_search", methods={"GET"})
     */
    public function searchCommits(Request $request): JsonResponse
    {
        $searchInput = $this->serializer->denormalize($request->query->all(), SearchInput::class);

        $data = $this->eventService->searchEvents($searchInput);

        return new JsonResponse($data);
    }
}
