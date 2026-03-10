<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\DTO\ItemDTO;
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
        $currentUser = $this->getCurrentUser();

        $response = $useCase->execute(
            new ListInventoryRequest($currentUser->id()->value())
        );

        return new JsonResponse([
            'items' => array_map(
                fn(ItemDTO $dto) => [
                    'id'             => $dto->id,
                    'name'           => $dto->name,
                    'estimatedHours' => $dto->estimatedHours,
                    'status'         => $dto->status,
                    'createdAt'      => $dto->createdAt,
                    'totalSessions'  => $dto->totalSessions,
                    'totalHours'     => $dto->totalHours,
                    'projectId'      => $dto->projectId,
                    'projectName'    => $dto->projectName,
                ],
                $response->items()
            ),
        ]);
    }
}
