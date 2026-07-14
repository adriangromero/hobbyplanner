<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Item\CreateItem\CreateItemRequest;
use App\Application\UseCase\Item\CreateItem\CreateItemUseCase;
use App\Application\UseCase\Item\DeleteItem\DeleteItemRequest;
use App\Application\UseCase\Item\DeleteItem\DeleteItemUseCase;
use App\Application\UseCase\Item\GetItemSessions\GetItemSessionsRequest;
use App\Application\UseCase\Item\GetItemSessions\GetItemSessionsUseCase;
use App\Application\UseCase\Item\UpdateItem\UpdateItemRequest;
use App\Application\UseCase\Item\UpdateItem\UpdateItemUseCase;
use App\Application\UseCase\Item\ToggleItemStatus\ToggleItemStatusRequest;
use App\Application\UseCase\Item\ToggleItemStatus\ToggleItemStatusUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/items')]
final class ItemController extends ApiController
{
    #[Route('', name: 'api_create_item', methods: ['POST'])]
    public function create(Request $request, CreateItemUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name', 'estimatedHours', 'projectId']);

        $response = $useCase->execute(new CreateItemRequest(
            projectId:      $data['projectId'],
            userId:         $this->currentUserId()->value(),
            name:           $data['name'],
            estimatedHours: (float) $data['estimatedHours'],
        ));

        return new JsonResponse($response->item()->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_item_update', methods: ['PUT'])]
    public function update(string $id, Request $request, UpdateItemUseCase $useCase): JsonResponse
    {
        $data = $this->jsonBody($request, ['name', 'estimatedHours']);

        $response = $useCase->execute(new UpdateItemRequest(
            itemId:         $id,
            name:           $data['name'],
            estimatedHours: (float) $data['estimatedHours'],
        ));

        return new JsonResponse($response->item()->toArray());
    }

    #[Route('/{id}/toggle-status', name: 'api_item_toggle_status', methods: ['PUT'])]
    public function toggleStatus(string $id, ToggleItemStatusUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new ToggleItemStatusRequest($id));

        return new JsonResponse($response->item()->toArray());
    }

    #[Route('/{id}', name: 'api_item_delete', methods: ['DELETE'])]
    public function delete(string $id, DeleteItemUseCase $useCase): JsonResponse
    {
        $useCase->execute(new DeleteItemRequest($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/sessions', name: 'api_item_sessions', methods: ['GET'])]
    public function sessions(string $id, GetItemSessionsUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(new GetItemSessionsRequest($id));

        return new JsonResponse([
            'sessions' => array_map(fn($s) => $s->toArray(), $response->sessions()),
        ]);
    }
}
