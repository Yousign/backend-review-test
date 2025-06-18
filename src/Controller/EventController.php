<?php

namespace App\Controller;

use App\Dto\EventInput;
use App\Repository\ReadEventRepository;
use App\Repository\WriteEventRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function count;

class EventController
{
    private WriteEventRepository $writeEventRepository;
    private ReadEventRepository $readEventRepository;
    private SerializerInterface $serializer;

    public function __construct(
        WriteEventRepository $writeEventRepository,
        ReadEventRepository $readEventRepository,
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

        if (count($errors) > 0) {
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
        } catch (Exception $exception) {
            return new Response("An error occurred while updating the event: " . $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
