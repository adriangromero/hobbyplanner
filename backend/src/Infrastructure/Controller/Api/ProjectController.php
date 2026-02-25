<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\DTO\ItemDTO;
use App\Application\DTO\ProjectDTO;
use App\Application\DTO\ProjectEstimationDTO;
use App\Application\DTO\WorkSessionDTO;
use App\Application\UseCase\Project\ListProjects\ListProjectsRequest;
use App\Application\UseCase\Project\ListProjects\ListProjectsUseCase;
use App\Application\UseCase\Project\GetProjectWithItems\GetProjectWithItemsRequest;
use App\Application\UseCase\Project\GetProjectWithItems\GetProjectWithItemsUseCase;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationRequest;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/projects')]
final class ProjectController
{
    #[Route('', name: 'api_projects_list', methods: ['GET'])]
    public function list(ListProjectsUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new ListProjectsRequest());

        return new JsonResponse([
            'projects' => array_map(
                fn(ProjectDTO $dto) => [
                    'id'          => $dto->id,
                    'name'        => $dto->name,
                    'description' => $dto->description,
                    'createdAt'   => $dto->createdAt,
                ],
                $response->projects()
            ),
        ]);
    }

    #[Route('/{id}', name: 'api_projects_detail', methods: ['GET'])]
    public function detail(string $id, GetProjectWithItemsUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetProjectWithItemsRequest($id));

        return new JsonResponse([
            'project' => [
                'id'          => $response->project()->id,
                'name'        => $response->project()->name,
                'description' => $response->project()->description,
                'createdAt'   => $response->project()->createdAt,
            ],
            'items' => array_map(
                fn(ItemDTO $dto) => [
                    'id'             => $dto->id,
                    'name'           => $dto->name,
                    'estimatedHours' => $dto->estimatedHours,
                    'totalSessions'  => $dto->totalSessions,
                    'totalHours'     => $dto->totalHours,
                    'sessions'       => array_map(
                        fn(WorkSessionDTO $s) => [
                            'id'            => $s->id,
                            'startedAt'     => $s->startedAt,
                            'endedAt'       => $s->endedAt,
                            'durationHours' => $s->durationHours,
                        ],
                        $dto->sessions
                    ),
                ],
                $response->items()
            ),
        ]);
    }

    #[Route('/{id}/estimation', name: 'api_projects_estimation', methods: ['GET'])]
    public function estimation(string $id, GetProjectEstimationUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetProjectEstimationRequest($id));

        $dto = $response->estimation();

        return new JsonResponse([
            'startDate'               => $dto->startDate,
            'estimatedHours'          => $dto->estimatedHours,
            'workedHours'             => $dto->workedHours,
            'remainingHours'          => $dto->remainingHours,
            'velocityPerDay'          => $dto->velocityPerDay,
            'daysRemaining'           => $dto->daysRemaining,
            'estimatedCompletionDate' => $dto->estimatedCompletionDate,
    ]);
    }
}