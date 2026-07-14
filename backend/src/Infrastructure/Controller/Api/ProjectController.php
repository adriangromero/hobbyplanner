<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Project\CreateProject\CreateProjectRequest;
use App\Application\UseCase\Project\CreateProject\CreateProjectUseCase;
use App\Application\UseCase\Project\DeleteProject\DeleteProjectRequest;
use App\Application\UseCase\Project\DeleteProject\DeleteProjectUseCase;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationRequest;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationUseCase;
use App\Application\UseCase\Project\GetProjectWithItems\GetProjectWithItemsRequest;
use App\Application\UseCase\Project\GetProjectWithItems\GetProjectWithItemsUseCase;
use App\Application\UseCase\Project\ListProjects\ListProjectsRequest;
use App\Application\UseCase\Project\ListProjects\ListProjectsUseCase;
use App\Application\UseCase\Project\ToggleProjectStatus\ToggleProjectStatusRequest;
use App\Application\UseCase\Project\ToggleProjectStatus\ToggleProjectStatusUseCase;
use App\Application\UseCase\Project\UpdateProject\UpdateProjectRequest;
use App\Application\UseCase\Project\UpdateProject\UpdateProjectUseCase;
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
        $response = $useCase->execute(new ListProjectsRequest($this->currentUserId()->value()));

        return new JsonResponse([
            'projects' => array_map(fn($dto) => $dto->toArray(), $response->projects()),
        ]);
    }

    #[Route('/{id}', name: 'api_projects_detail', methods: ['GET'])]
    public function detail(string $id, Request $request, GetProjectWithItemsUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetProjectWithItemsRequest(
            projectId: $id,
            sortBy:    $request->query->get('sortBy'),
            direction: $request->query->get('direction'),
        ));

        return new JsonResponse([
            'project' => $response->project()->toArray(),
            'items'   => array_map(fn($dto) => $dto->toArray(), $response->items()),
        ]);
    }

    #[Route('/{id}/estimation', name: 'api_projects_estimation', methods: ['GET'])]
    public function estimation(string $id, GetProjectEstimationUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetProjectEstimationRequest($id));

        return new JsonResponse($response->estimation()->toArray());
    }

    #[Route('', name: 'api_create_project', methods: ['POST'])]
    public function create(Request $request, CreateProjectUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name', 'description']);

        $response = $useCase->execute(new CreateProjectRequest(
            userId:      $this->currentUserId()->value(),
            name:        $data['name'],
            description: $data['description'],
        ));

        return new JsonResponse($response->project()->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_project_update', methods: ['PUT'])]
    public function update(string $id, Request $request, UpdateProjectUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name', 'description']);

        $response = $useCase->execute(new UpdateProjectRequest(
            projectId:   $id,
            name:        $data['name'],
            description: $data['description'],
        ));

        return new JsonResponse($response->project()->toArray());
    }

    #[Route('/{id}/toggle-status', name: 'api_project_toggle_status', methods: ['PUT'])]
    public function toggleStatus(string $id, ToggleProjectStatusUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new ToggleProjectStatusRequest($id));

        return new JsonResponse($response->project()->toArray());
    }

    #[Route('/{id}', name: 'api_project_delete', methods: ['DELETE'])]
    public function delete(string $id, DeleteProjectUseCase $useCase): JsonResponse
    {
        $useCase->execute(new DeleteProjectRequest($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
