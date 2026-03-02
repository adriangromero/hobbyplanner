<?php

declare(strict_types=1);

namespace App\Application\UseCase\Item\CreateItem;

use App\Application\DTO\ItemDTO;
use App\Domain\Entity\Item;
use App\Domain\Repository\ItemRepositoryInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Application\UseCase\Item\CreateItem\CreateItemResponse;
use App\Application\UseCase\Item\CreateItem\CreateItemRequest;
use App\Domain\ValueObject\ItemId;
use App\Domain\Exception\ProjectNotFoundException;

final class CreateItemUseCase
{
    public function __construct(
        private readonly ProjectRepositoryInterface     $projectRepository,
        private readonly ItemRepositoryInterface        $itemRepository,
    ) {}

    public function execute(CreateItemRequest $request): CreateItemResponse
    {
        // 1. Validar que existen las dependencias
        $project = $this->projectRepository->findById($request->projectId());
        if ($project === null) {
            throw new ProjectNotFoundException($request->projectId()->value());
        }

        // 2. Crear la entidad — validación en el constructor
        $item = new Item(
            ItemId::create(),
            $request->projectId(),
            $request->userId(),
            $request->name(),
            $request->estimatedHours(),
        );

        // 3. Persistir
        $this->itemRepository->save($item);

        // 4. Devolver Response con DTO
        return new CreateItemResponse(
            ItemDTO::fromEntity($item, 0, [])
        );
    }

}
