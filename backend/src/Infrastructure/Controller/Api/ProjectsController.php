<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Project\ListProjects\ListProjectsRequest;
use App\Application\UseCase\Project\ListProjects\ListProjectsUseCase;
use App\Application\UseCase\Project\GetProjectFull\GetProjectFullRequest;
use App\Application\UseCase\Project\GetProjectFull\GetProjectFullUseCase;
use App\Application\UseCase\Project\GetProjectEstimation\GetProjectEstimationUseCase;
use App\Domain\ValueObject\ProjectId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projects')]
final class ProjectsController
{
    #[Route('', name: 'api_projects_list', methods: ['GET'])]
    public function list(ListProjectsUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new ListProjectsRequest());

        return new JsonResponse([
            'projects' => array_map(fn($p) => [
                'id' => $p->id()->value(),
                'name' => $p->name(),
                'description' => $p->description(),
                'createdAt' => $p->createdAt()->format('c'),
            ], $response->projects())
        ]);
    }

    #[Route('/{id}/estimation', methods: ['GET'])]
    public function estimation(string $id, GetProjectEstimationUseCase $useCase): JsonResponse
    {
        $projectId = ProjectId::fromString($id);

        $estimation = $useCase->execute($projectId);

        return new JsonResponse($estimation->toArray());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function full(string $id, GetProjectFullUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetProjectFullRequest($id));

        return new JsonResponse([
            'project' => [
                'id' => $response->project()->id()->value(),
                'name' => $response->project()->name(),
                'description' => $response->project()->description(),
                'createdAt' => $response->project()->createdAt()->format('c'),
            ],
            'items' => array_map(fn($i) => [
                'id' => $i->id()->value(),
                'name' => $i->name(),
                'estimatedHours' => $i->estimatedHours(),
            ], $response->items()),
            'sessions' => array_map(fn($s) => [
                'id' => $s->id()->value(),
                'itemId' => $s->itemId()->value(),
                'createdAt' => $s->startedAt()->format('c'),
            ], $response->sessions())
        ]);
    }

}
