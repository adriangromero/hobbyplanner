<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\DTO\ItemDTO;
use App\Application\DTO\ProjectDTO;
use App\Application\UseCase\Project\ListProjects\ListProjectsRequest;
use App\Application\UseCase\Project\ListProjects\ListProjectsUseCase;
use App\Application\UseCase\Project\GetProjectWithItems\GetProjectWithItemsRequest;
use App\Application\UseCase\Project\GetProjectWithItems\GetProjectWithItemsUseCase;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationRequest;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationUseCase;
use App\Application\UseCase\Project\CreateProject\CreateProjectRequest;
use App\Application\UseCase\Project\CreateProject\CreateProjectUseCase;
use App\Application\UseCase\Project\UpdateProject\UpdateProjectRequest;
use App\Application\UseCase\Project\UpdateProject\UpdateProjectUseCase;
use App\Application\UseCase\Project\DeleteProject\DeleteProjectRequest;
use App\Application\UseCase\Project\DeleteProject\DeleteProjectUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/projects')]
final class ProjectController extends ApiController
{
    #[Route('', name: 'api_projects_list', methods: ['GET'])]
    public function list(ListProjectsUseCase $useCase): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        $response = $useCase->execute(new ListProjectsRequest($currentUser->id()->value()));

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
                    'openSession'    => $dto->openSession ? [
                        'id'        => $dto->openSession->id,
                        'startedAt' => $dto->openSession->startedAt,
                    ] : null,
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
            'velocityPerActiveDay'    => $dto->velocityPerActiveDay,
            'activeDays'              => $dto->activeDays,
            'frequencyDaysPerWeek'    => $dto->frequencyDaysPerWeek,
            'activeDaysRemaining'     => $dto->activeDaysRemaining,
            'daysRemaining'           => $dto->daysRemaining,
            'estimatedCompletionDate' => $dto->estimatedCompletionDate,
        ]);
    }

    #[Route('', name: 'api_create_project', methods: ['POST'])]
    public function create(
        Request $request,
        CreateProjectUseCase $useCase,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $this->validateRequired($data, ['name', 'description']);

        $currentUser = $this->getCurrentUser();

        $response = $useCase->execute(new CreateProjectRequest(
            userId:      $currentUser->id()->value(),
            name:        $data['name'],
            description: $data['description'],
        ));

        $dto = $response->project();

        return new JsonResponse([
            'id'          => $dto->id,
            'name'        => $dto->name,
            'description' => $dto->description,
            'createdAt'   => $dto->createdAt,
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_project_update', methods: ['PUT'])]
    public function update(
        string $id,
        Request $request,
        UpdateProjectUseCase $useCase,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $this->validateRequired($data, ['name', 'description']);

        $response = $useCase->execute(new UpdateProjectRequest(
            projectId:   $id,
            name:        $data['name'],
            description: $data['description'],
        ));

        $dto = $response->project();

        return new JsonResponse([
            'id'          => $dto->id,
            'name'        => $dto->name,
            'description' => $dto->description,
            'createdAt'   => $dto->createdAt,
        ]);
    }

    #[Route('/{id}', name: 'api_project_delete', methods: ['DELETE'])]
    public function delete(
        string $id,
        DeleteProjectUseCase $useCase,
    ): JsonResponse {
        $useCase->execute(new DeleteProjectRequest($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
