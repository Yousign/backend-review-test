<?php

namespace App\Application\Controller;

use App\Application\Dto\EventInput;
use App\Domain\Repository\ReadEventRepositoryInterface;
use App\Domain\Repository\WriteEventRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EventController
{
    private WriteEventRepositoryInterface $writeEventRepository;
    private ReadEventRepositoryInterface $readEventRepository;
    private SerializerInterface $serializer;

    public function __construct(
        WriteEventRepositoryInterface $writeEventRepository,
        ReadEventRepositoryInterface $readEventRepository,
        SerializerInterface $serializer
    ) {
        $this->writeEventRepository = $writeEventRepository;
        $this->readEventRepository = $readEventRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route(path="/api/event/{id}/update", name="api_commit_update", methods={"PUT"})
     */
    public function update(Request $request, int $id, ValidatorInterface $validator): Response
    {
        $eventInput = $this->serializer->deserialize($request->getContent(), EventInput::class, 'json');

        $errors = $validator->validate($eventInput);

        if (\count($errors) > 0) {
            return new JsonResponse(
                ['message' => $errors->get(0)->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        if($this->readEventRepository->exist($id) === false) {
            return new JsonResponse(
                ['message' => sprintf('Event identified by %d not found !', $id)],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $this->writeEventRepository->update($eventInput, $id);
        } catch (\Exception $exception) {
            return new Response(null, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
