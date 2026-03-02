<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\WorkSession\StartWorkSession\StartWorkSessionRequest;
use App\Application\UseCase\WorkSession\StartWorkSession\StartWorkSessionUseCase;
use App\Application\UseCase\WorkSession\FinishWorkSession\FinishWorkSessionRequest;
use App\Application\UseCase\WorkSession\FinishWorkSession\FinishWorkSessionUseCase;
use App\Application\UseCase\WorkSession\UpdateWorkSession\UpdateWorkSessionRequest;
use App\Application\UseCase\WorkSession\UpdateWorkSession\UpdateWorkSessionUseCase;
use App\Application\UseCase\WorkSession\DeleteWorkSession\DeleteWorkSessionRequest;
use App\Application\UseCase\WorkSession\DeleteWorkSession\DeleteWorkSessionUseCase;
use App\Domain\Exception\ItemNotFoundException;
use App\Domain\Exception\ProjectNotFoundException;
use App\Domain\Exception\WorkSessionNotFoundException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/work-sessions')]
final class WorkSessionController extends ApiController
{
    #[Route('', name: 'api_work_sessions_start', methods: ['POST'])]
    public function start(
        Request $request,
        StartWorkSessionUseCase $useCase,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $error = $this->validateRequired($data, ['itemId', 'projectId']);
        if ($error) return $error;

        $currentUser = $this->getCurrentUser();

        try {
            $response = $useCase->execute(new StartWorkSessionRequest(
                itemId:    $data['itemId'],
                projectId: $data['projectId'],
                userId:    $currentUser->id()->value(),
            ));

            $dto = $response->session();

            return new JsonResponse([
                'id'        => $dto->id,
                'itemId'    => $dto->itemId,
                'projectId' => $dto->projectId,
                'startedAt' => $dto->startedAt,
            ], Response::HTTP_CREATED);

        } catch (ItemNotFoundException | ProjectNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    #[Route('/{id}/finish', name: 'api_work_sessions_finish', methods: ['PUT'])]
    public function finish(
        string $id,
        FinishWorkSessionUseCase $useCase,
    ): JsonResponse {
        try {
            $response = $useCase->execute(new FinishWorkSessionRequest($id));

            $dto = $response->session();

            return new JsonResponse([
                'id'            => $dto->id,
                'itemId'        => $dto->itemId,
                'startedAt'     => $dto->startedAt,
                'endedAt'       => $dto->endedAt,
                'durationHours' => $dto->durationHours,
            ]);

        } catch (WorkSessionNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    #[Route('/{id}', name: 'api_work_sessions_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
        UpdateWorkSessionUseCase $useCase,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['startedAt'])) {
            return new JsonResponse(
                ['error' => 'Falta campo requerido: startedAt'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $response = $useCase->execute(new UpdateWorkSessionRequest(
                sessionId: $id,
                startedAt: $data['startedAt'],
                endedAt:   $data['endedAt'] ?? null,
            ));

            $dto = $response->session();

            return new JsonResponse([
                'id'            => $dto->id,
                'itemId'        => $dto->itemId,
                'startedAt'     => $dto->startedAt,
                'endedAt'       => $dto->endedAt,
                'durationHours' => $dto->durationHours,
            ]);

        } catch (WorkSessionNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    #[Route('/{id}', name: 'api_work_sessions_delete', methods: ['DELETE'])]
    public function delete(
        string $id,
        DeleteWorkSessionUseCase $useCase,
    ): JsonResponse {
        try {
            $useCase->execute(new DeleteWorkSessionRequest($id));

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        } catch (WorkSessionNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}