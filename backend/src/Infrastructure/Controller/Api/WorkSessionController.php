<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\WorkSession\DeleteWorkSession\DeleteWorkSessionRequest;
use App\Application\UseCase\WorkSession\DeleteWorkSession\DeleteWorkSessionUseCase;
use App\Application\UseCase\WorkSession\FinishWorkSession\FinishWorkSessionRequest;
use App\Application\UseCase\WorkSession\FinishWorkSession\FinishWorkSessionUseCase;
use App\Application\UseCase\WorkSession\StartWorkSession\StartWorkSessionRequest;
use App\Application\UseCase\WorkSession\StartWorkSession\StartWorkSessionUseCase;
use App\Application\UseCase\WorkSession\UpdateWorkSession\UpdateWorkSessionRequest;
use App\Application\UseCase\WorkSession\UpdateWorkSession\UpdateWorkSessionUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/work-sessions')]
final class WorkSessionController extends ApiController
{
    #[Route('', name: 'api_work_sessions_start', methods: ['POST'])]
    public function start(Request $request, StartWorkSessionUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['itemId', 'projectId']);

        $response = $useCase->execute(new StartWorkSessionRequest(
            itemId:    $data['itemId'],
            projectId: $data['projectId'],
            userId:    $this->currentUserId()->value(),
        ));

        return new JsonResponse($response->session()->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}/finish', name: 'api_work_sessions_finish', methods: ['PUT'])]
    public function finish(string $id, FinishWorkSessionUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new FinishWorkSessionRequest($id));

        return new JsonResponse($response->session()->toArray());
    }

    #[Route('/{id}', name: 'api_work_sessions_update', methods: ['PUT'])]
    public function update(string $id, Request $request, UpdateWorkSessionUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['startedAt']);

        $response = $useCase->execute(new UpdateWorkSessionRequest(
            sessionId: $id,
            startedAt: $data['startedAt'],
            endedAt:   $data['endedAt'] ?? null,
        ));

        return new JsonResponse($response->session()->toArray());
    }

    #[Route('/{id}', name: 'api_work_sessions_delete', methods: ['DELETE'])]
    public function delete(string $id, DeleteWorkSessionUseCase $useCase): JsonResponse
    {
        $useCase->execute(new DeleteWorkSessionRequest($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
