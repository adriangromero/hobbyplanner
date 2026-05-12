<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Inventory\ListInventoryRequest;
use App\Application\UseCase\Inventory\ListInventoryUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/inventory')]
final class InventoryController extends ApiController
{
    #[Route('', name: 'api_inventory_list', methods: ['GET'])]
    public function list(ListInventoryUseCase $useCase): JsonResponse
    {
        $response = $useCase->execute(
            new ListInventoryRequest($this->currentUserId()->value())
        );

        return new JsonResponse([
            'items' => array_map(fn($dto) => $dto->toArray(), $response->items()),
        ]);
    }
}
