<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Project\GetProjectFull\GetProjectFullRequest;
use App\Application\UseCase\Project\GetProjectFull\GetProjectFullUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/items')]
final class ItemsController
{
    #[Route('/byProject/{projectId}', name: 'api_items_list_by_project', methods: ['GET'])]
    public function listByProject(string $projectId, GetProjectFullUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetProjectFullRequest($projectId));

        return new JsonResponse([
            'items' => array_map(fn($i) => [
                'id' => $i->id()->value(),
                'name' => $i->name(),
                'estimatedHours' => $i->estimatedHours(),
            ], $response->items())
        ]);
    }
}
